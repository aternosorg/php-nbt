<?php

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\Deserializer\NbtDeserializer;
use Exception;
use JsonSerializable;

abstract class Tag implements JsonSerializable
{
    public const TYPE = -1;

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

    /**
     * @param string|null $name
     * @return $this
     */
    public function setName(?string $name): Tag
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
     * @return bool
     */
    public function canBeNamed(): bool
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
        if ($named && $this->canBeNamed()) {
            $nameLength = $reader->getDeserializer()->readStringLengthPrefix()->getValue();
            $name = $reader->read($nameLength);
            if (strlen($name) !== $nameLength) {
                throw new Exception("Failed to read name of " . static::class);
            }
            $this->setName($name);
        }
        return $this->readContent($reader);
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
        if ($named && $this->canBeNamed()) {
            $name = $this->getName();
            if (is_null($name)) {
                throw new Exception("Cannot write named tag, because tag does not have a name value");
            }
            $serializer->writeStringLengthPrefix(strlen($this->getName()));
            $writer->write($this->getName());
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
     * @return string
     */
    protected function indent(string $str): string
    {
        return "  " . str_replace("\n", "\n  ", $str);
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
     * @return string|null
     */
    public static function getTagClass(int $type): ?string
    {
        return static::TAGS[$type] ?? null;
    }

    /**
     * @param Reader $reader
     * @param bool $named
     * @return Tag
     * @throws Exception
     */
    public static function load(Reader $reader, bool $named = true): Tag
    {
        $type = $reader->getDeserializer()->readByte()->getValue();
        $class = static::getTagClass($type);
        if (is_null($class)) {
            throw new Exception("Unknown NBT tag type " . $type);
        }
        /** @var Tag $tag */
        $tag = new $class();
        $tag->read($reader, $named);
        return $tag;
    }
}
