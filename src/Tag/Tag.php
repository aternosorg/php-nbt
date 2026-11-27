<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\Exception\CircularStructureException;
use Aternos\Nbt\Exception\InvalidTagValueException;
use Aternos\Nbt\Exception\NbtException;
use Aternos\Nbt\Exception\UnknownTagTypeException;
use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Aternos\Nbt\NbtFormat;
use BadMethodCallException;
use JsonSerializable;

abstract class Tag implements JsonSerializable
{
    /**
     * The type of the tag, set by every concrete tag class
     */
    public const ?TagType TYPE = null;

    protected ?string $name = null;
    protected bool $isBeingSerialized = false;
    protected ?Tag $parentTag = null;
    protected TagOptions $options;

    /**
     * @param TagOptions|null $options
     */
    public function __construct(?TagOptions $options = null)
    {
        $this->options = $options ?: new TagOptions();
    }

    /**
     * @param string|null $name
     * @return $this
     */
    public function setName(?string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param Tag|null $parentTag
     * @return $this
     */
    public function setParentTag(?Tag $parentTag): static
    {
        $this->parentTag = $parentTag;
        return $this;
    }

    /**
     * @return Tag|null
     */
    public function getParentTag(): ?Tag
    {
        return $this->parentTag;
    }

    /**
     * @return array<?string>
     * @throws CircularStructureException
     */
    public function getPath(): array
    {
        if ($this->isBeingSerialized) {
            throw new CircularStructureException("Failed to resolve path: Circular NBT structure detected");
        }
        $this->isBeingSerialized = true;
        try {
            if($this->parentTag) {
                return [...$this->parentTag->getPath(), $this->getName()];
            }
            return [$this->getName()];
        } finally {
            $this->isBeingSerialized = false;
        }
    }

    /**
     * @return string|null
     */
    public function getStringPath(): ?string
    {
        try {
            $path = $this->getPath();
        }catch (CircularStructureException) {
            return null;
        }
        return implode("/", $path);
    }

    /**
     * @return bool
     */
    public static function canBeNamed(): bool
    {
        return true;
    }

    /**
     * @param Writer $writer
     * @return $this
     */
    abstract public function writeContent(Writer $writer): static;

    /**
     * @param Reader $reader
     * @return $this
     */
    abstract protected function readContent(Reader $reader): static;

    /**
     * @param Reader $reader
     * @param TagOptions $options
     * @return string
     */
    protected static function readContentRaw(Reader $reader, TagOptions $options): string
    {
        throw new BadMethodCallException("Not implemented");
    }

    public function getType(): TagType
    {
        return static::TYPE;
    }

    /**
     * Read tag name and payload
     *
     * @param Reader $reader
     * @param bool $named
     * @return $this
     * @throws NbtException
     */
    public function read(Reader $reader, bool $named = true): static
    {
        if ($named && static::canBeNamed()) {
            $name = $reader->getDeserializer()->readString();
            $this->setName($name->getValue());
        }
        return $this->readContent($reader);
    }

    /**
     * @param Reader $reader
     * @param TagOptions $options
     * @param bool $named
     * @return string
     * @throws NbtException
     */
    public static function readRaw(Reader $reader, TagOptions $options, bool $named = true): string
    {
        $result = "";
        if ($named && static::canBeNamed()) {
            $name = $reader->getDeserializer()->readString();
            $result .= $name->getRawData();
        }
        $result .= static::readContentRaw($reader, $options);
        return $result;
    }

    /**
     * @param Writer $writer
     * @param bool $named
     * @return $this
     * @throws NbtException
     */
    public function writeData(Writer $writer, bool $named = true): static
    {
        $writer->write(pack("C", $this->getType()->value));
        $serializer = $writer->getSerializer();
        if ($named && static::canBeNamed()) {
            $name = $this->getName();
            if (is_null($name)) {
                throw new InvalidTagValueException("Cannot write named tag, because tag does not have a name value");
            }
            $serializer->writeString($this->getName());
        }
        $this->writeGuardedContent($writer);
        return $this;
    }

    /**
     * Write the content of this tag and throw instead of recursing forever if the tag contains itself
     *
     * Used for named tags and for list entries, so every way a tag can contain another tag is checked.
     *
     * @throws NbtException
     */
    protected function writeGuardedContent(Writer $writer): void
    {
        if ($this->isBeingSerialized) {
            throw new CircularStructureException("Failed to serialize " . $this->getType()->getName() .
                ($this->name !== null ? " '" . $this->name . "'" : "") . ": Circular NBT structure detected");
        }
        $this->isBeingSerialized = true;
        try {
            $this->writeContent($writer);
        } finally {
            // Also reset after a failed write, otherwise the next write would report a circular structure
            $this->isBeingSerialized = false;
        }
    }

    /**
     * @param Writer $writer
     * @return $this
     * @throws NbtException
     */
    public function write(Writer $writer): static
    {
        if (!($this instanceof CompoundTag) &&
            !(in_array($writer->getFormat(), [NbtFormat::BEDROCK_EDITION_NETWORK, NbtFormat::BEDROCK_EDITION]) &&
                $this instanceof ListTag)) {
            throw new InvalidTagValueException("NBT files must start with a CompoundTag (or ListTag for Minecraft Bedrock Edition)");
        }
        if ($this->getName() === null) {
            $this->setName("");
        }
        $this->writeData($writer);
        return $this;
    }

    /**
     * @param string $str
     * @param int $width
     * @return string
     */
    protected function indent(string $str, int $width = 2): string
    {
        return str_repeat(" ", $width) . str_replace("\n", "\n  ", $str);
    }

    /**
     * @return string
     */
    protected function getTagTypeString(): string
    {
        return $this->getType()->getName();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getTagTypeString() . "('" . ($this->getName() ?: "None") . "'): " . $this->getValueString();
    }

    /**
     * Convert tag to SNBT
     * See https://minecraft.wiki/w/NBT_format#Conversion_to_SNBT
     *
     * @return string
     */
    abstract public function toSNBT(): string;

    /**
     * @param Tag $tag
     * @return bool
     */
    abstract function equals(Tag $tag): bool;

    /**
     * @return string
     */
    abstract protected function getValueString(): string;

    /**
     * Read a complete tag, starting with its type
     *
     * @throws NbtException
     */
    public static function load(Reader $reader, ?TagOptions $options = null, ?Tag $parent = null): Tag
    {
        if($options === null) {
            $options = new TagOptions();
        }

        $typeId = $reader->getDeserializer()->readByte()->getValue();
        $class = static::getTagTypeById($typeId)->getTagClass();
        $tag = new $class($options);
        $tag->setParentTag($parent)->read($reader);
        return $tag;
    }

    /**
     * @param Reader $reader
     * @param TagOptions|null $options
     * @return RawTagReadResult
     * @throws NbtException
     */
    public static function loadRaw(Reader $reader, ?TagOptions $options = null): RawTagReadResult
    {
        if($options === null) {
            $options = new TagOptions();
        }

        $typeByte = $reader->getDeserializer()->readByte();
        $type = static::getTagTypeById($typeByte->getValue());
        $data = $type->getTagClass()::readRaw($reader, $options);
        return new RawTagReadResult($typeByte->getRawData() . $data, $type);
    }

    /**
     * @throws UnknownTagTypeException
     */
    protected static function getTagTypeById(int $typeId): TagType
    {
        return TagType::tryFrom($typeId) ?? throw new UnknownTagTypeException("Unknown NBT tag type " . $typeId);
    }
}
