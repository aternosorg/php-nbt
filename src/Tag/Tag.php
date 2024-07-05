<?php

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Aternos\Nbt\NbtFormat;
use BadMethodCallException;
use Exception;
use JsonSerializable;

abstract class Tag implements JsonSerializable
{
    public const TYPE = -1;

    /**
     * @var class-string<Tag>[]
     */
    public const TAGS = [
        TagType::TAG_End => EndTag::class,
        TagType::TAG_Byte => ByteTag::class,
        TagType::TAG_Short => ShortTag::class,
        TagType::TAG_Int => IntTag::class,
        TagType::TAG_Long => LongTag::class,
        TagType::TAG_Float => FloatTag::class,
        TagType::TAG_Double => DoubleTag::class,
        TagType::TAG_Byte_Array => ByteArrayTag::class,
        TagType::TAG_String => StringTag::class,
        TagType::TAG_List => ListTag::class,
        TagType::TAG_Compound => CompoundTag::class,
        TagType::TAG_Int_Array => IntArrayTag::class,
        TagType::TAG_Long_Array => LongArrayTag::class
    ];

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
     * @throws Exception
     */
    public function getPath(): array
    {
        if ($this->isBeingSerialized) {
            throw new Exception("Failed to resolve path: Circular NBT structure detected");
        }
        $this->isBeingSerialized = true;
        if($this->parentTag) {
            $path = [...$this->parentTag->getPath(), $this->getName()];
        }else {
            $path = [$this->getName()];
        }
        $this->isBeingSerialized = false;
        return $path;
    }

    /**
     * @return string|null
     */
    public function getStringPath(): ?string
    {
        try {
            $path = $this->getPath();
        }catch (Exception) {
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

    /**
     * @return int
     */
    public function getType(): int
    {
        return static::TYPE;
    }

    /**
     * Read tag name and payload
     *
     * @param Reader $reader
     * @param bool $named
     * @return $this
     * @throws Exception
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
     * @throws Exception
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
     * @throws Exception
     */
    public function writeData(Writer $writer, bool $named = true): static
    {
        if ($this->isBeingSerialized) {
            throw new Exception("Failed to serialize: Circular NBT structure detected");
        }
        $this->isBeingSerialized = true;
        $writer->write(pack("C", static::TYPE & 0xff));
        $serializer = $writer->getSerializer();
        if ($named && static::canBeNamed()) {
            $name = $this->getName();
            if (is_null($name)) {
                throw new Exception("Cannot write named tag, because tag does not have a name value");
            }
            $serializer->writeString($this->getName());
        }
        $this->writeContent($writer);
        $this->isBeingSerialized = false;
        return $this;
    }

    /**
     * @param Writer $writer
     * @return $this
     * @throws Exception
     */
    public function write(Writer $writer): static
    {
        if (!($this instanceof CompoundTag) &&
            !(in_array($writer->getFormat(), [NbtFormat::BEDROCK_EDITION_NETWORK, NbtFormat::BEDROCK_EDITION]) &&
                $this instanceof ListTag)) {
            throw new Exception("NBT files must start with a CompoundTag (or ListTag for Minecraft Bedrock Edition)");
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
        return TagType::NAMES[static::TYPE];
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
     * @param int $type
     * @return class-string<Tag>|null
     */
    public static function getTagClass(int $type): ?string
    {
        return static::TAGS[$type] ?? null;
    }

    /**
     * @param Reader $reader
     * @param TagOptions|null $options
     * @param Tag|null $parent
     * @return Tag
     * @throws Exception
     */
    public static function load(Reader $reader, ?TagOptions $options = null, Tag $parent = null): Tag
    {
        if($options === null) {
            $options = new TagOptions();
        }

        $type = $reader->getDeserializer()->readByte()->getValue();
        $class = static::getTagClass($type);
        if (is_null($class)) {
            throw new Exception("Unknown NBT tag type " . $type);
        }
        /** @var Tag $tag */
        $tag = new $class($options);
        $tag->setParentTag($parent)->read($reader);
        return $tag;
    }

    /**
     * @param Reader $reader
     * @param TagOptions|null $options
     * @return RawTagReadResult
     * @throws Exception
     */
    public static function loadRaw(Reader $reader, ?TagOptions $options = null): RawTagReadResult
    {
        if($options === null) {
            $options = new TagOptions();
        }

        $type = $reader->getDeserializer()->readByte();
        $class = static::getTagClass($type->getValue());
        if (is_null($class)) {
            throw new Exception("Unknown NBT tag type " . $type->getValue());
        }
        /** @var Tag $class */
        $data = $class::readRaw($reader, $options);
        return new RawTagReadResult($type->getRawData() . $data, $type->getValue());
    }
}
