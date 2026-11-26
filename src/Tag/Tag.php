<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\Exception\CircularStructureException;
use Aternos\Nbt\Exception\InvalidTagValueException;
use Aternos\Nbt\Exception\NbtException;
use Aternos\Nbt\Exception\UnknownTagTypeException;
use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use JsonSerializable;
use Stringable;

/**
 * Reading and writing tags can also throw a \pocketmine\utils\BinaryDataException if the input data ends unexpectedly.
 */
abstract class Tag implements JsonSerializable, Stringable
{
    protected ?string $name = null;
    protected bool $isBeingSerialized = false;
    protected ?Tag $parentTag = null;
    protected TagOptions $options;

    public function __construct(?TagOptions $options = null)
    {
        $this->options = $options ?? new TagOptions();
    }

    abstract public function getType(): TagType;

    public function setName(?string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setParentTag(?Tag $parentTag): static
    {
        $this->parentTag = $parentTag;
        return $this;
    }

    public function getParentTag(): ?Tag
    {
        return $this->parentTag;
    }

    /**
     * Names of all parent tags and this tag, starting at the root tag
     *
     * @return list<?string>
     * @throws CircularStructureException
     */
    public function getPath(): array
    {
        $names = [];
        $visited = [];
        for ($tag = $this; $tag !== null; $tag = $tag->parentTag) {
            if (isset($visited[spl_object_id($tag)])) {
                throw new CircularStructureException("Failed to resolve path: " . $this->getType()->getName() . " '" . $this->name . "' is its own parent");
            }
            $visited[spl_object_id($tag)] = true;
            $names[] = $tag->name;
        }
        return array_reverse($names);
    }

    /**
     * Path of this tag with names joined by "/", null if the structure is circular
     */
    public function getStringPath(): ?string
    {
        try {
            $path = $this->getPath();
        } catch (CircularStructureException) {
            return null;
        }
        return implode("/", $path);
    }

    public static function canBeNamed(): bool
    {
        return true;
    }

    /**
     * Write the tag payload, without type byte and name
     *
     * @throws NbtException
     */
    abstract protected function writeContent(Writer $writer): void;

    /**
     * Read the tag payload, without type byte and name
     *
     * @throws NbtException
     */
    abstract protected function readContent(Reader $reader): void;

    /**
     * Read the tag payload without parsing it
     *
     * @throws NbtException
     */
    abstract protected static function readContentRaw(Reader $reader, TagOptions $options): string;

    /**
     * Read tag name and payload
     *
     * @throws NbtException
     */
    public function read(Reader $reader, bool $named = true): static
    {
        if ($named && static::canBeNamed()) {
            $this->setName($reader->getDeserializer()->readString()->value);
        }
        $this->readContent($reader);
        return $this;
    }

    /**
     * Read tag name and payload without parsing them
     *
     * @throws NbtException
     */
    public static function readRaw(Reader $reader, TagOptions $options, bool $named = true): string
    {
        $result = "";
        if ($named && static::canBeNamed()) {
            $result .= $reader->getDeserializer()->readRawString();
        }
        return $result . static::readContentRaw($reader, $options);
    }

    /**
     * Write type byte, name (if $named) and payload
     *
     * @throws NbtException
     */
    public function writeData(Writer $writer, bool $named = true): static
    {
        $serializer = $writer->getSerializer();
        $serializer->writeByte($this->getType()->value);
        if ($named && static::canBeNamed()) {
            if ($this->name === null) {
                throw new InvalidTagValueException("Cannot write named " . $this->getType()->getName() . " because it does not have a name");
            }
            $serializer->writeString($this->name);
        }
        $this->writeGuardedContent($writer);
        return $this;
    }

    /**
     * Write this tag as the root tag of an NBT file
     *
     * @throws NbtException
     */
    public function write(Writer $writer): static
    {
        $isAllowedRoot = $this instanceof CompoundTag || ($this instanceof ListTag && $writer->getFormat()->allowsListAsRoot());
        if (!$isAllowedRoot) {
            throw new InvalidTagValueException("NBT files must start with a CompoundTag (or ListTag for Minecraft Bedrock Edition), got " . $this->getType()->getName());
        }
        $this->name ??= "";
        $this->writeData($writer);
        return $this;
    }

    /**
     * Write the payload and fail instead of recursing endlessly if this tag contains itself
     *
     * @throws NbtException
     */
    protected function writeGuardedContent(Writer $writer): void
    {
        if ($this->isBeingSerialized) {
            throw new CircularStructureException("Failed to serialize: " . $this->getType()->getName() . " '" . $this->name . "' contains itself");
        }
        $this->isBeingSerialized = true;
        try {
            $this->writeContent($writer);
        } finally {
            $this->isBeingSerialized = false;
        }
    }

    protected function indent(string $str, int $width = 2): string
    {
        $indentation = str_repeat(" ", $width);
        return $indentation . str_replace("\n", "\n" . $indentation, $str);
    }

    protected function getTagTypeString(): string
    {
        return $this->getType()->getName();
    }

    public function __toString(): string
    {
        return $this->getTagTypeString() . "('" . ($this->name ?: "None") . "'): " . $this->getValueString();
    }

    /**
     * Convert tag to SNBT
     * See https://minecraft.wiki/w/NBT_format#Conversion_to_SNBT
     */
    abstract public function toSNBT(): string;

    /**
     * Compare type and value, names are ignored
     */
    abstract public function equals(Tag $tag): bool;

    abstract protected function getValueString(): string;

    /**
     * Read a tag type byte
     *
     * @return array{TagType, string} the tag type and the raw byte
     * @throws UnknownTagTypeException
     */
    protected static function readTagType(Reader $reader): array
    {
        $type = $reader->getDeserializer()->readByte();
        $tagType = TagType::tryFrom($type->value);
        if ($tagType === null) {
            throw new UnknownTagTypeException("Unknown NBT tag type " . $type->value . " at offset " . ($reader->tell() - 1));
        }
        return [$tagType, $type->rawData];
    }

    /**
     * Read a complete named tag
     *
     * @throws NbtException
     */
    public static function load(Reader $reader, ?TagOptions $options = null, ?Tag $parent = null): Tag
    {
        [$type] = static::readTagType($reader);
        return $type->createTag($options ?? new TagOptions())
            ->setParentTag($parent)
            ->read($reader);
    }

    /**
     * Read a complete named tag without parsing it
     *
     * @throws NbtException
     */
    public static function loadRaw(Reader $reader, ?TagOptions $options = null): RawTagReadResult
    {
        [$type, $rawType] = static::readTagType($reader);
        $data = $type->getTagClass()::readRaw($reader, $options ?? new TagOptions());
        return new RawTagReadResult($rawType . $data, $type);
    }
}
