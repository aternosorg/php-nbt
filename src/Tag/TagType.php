<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

/**
 * NBT tag types, backed by the type byte used in serialized NBT data
 */
enum TagType: int
{
    case END = 0;
    case BYTE = 1;
    case SHORT = 2;
    case INT = 3;
    case LONG = 4;
    case FLOAT = 5;
    case DOUBLE = 6;
    case BYTE_ARRAY = 7;
    case STRING = 8;
    case LIST = 9;
    case COMPOUND = 10;
    case INT_ARRAY = 11;
    case LONG_ARRAY = 12;

    /**
     * Name of the tag type as used in the NBT specification, e.g. "TAG_Byte_Array"
     */
    public function getName(): string
    {
        return match ($this) {
            self::END => "TAG_End",
            self::BYTE => "TAG_Byte",
            self::SHORT => "TAG_Short",
            self::INT => "TAG_Int",
            self::LONG => "TAG_Long",
            self::FLOAT => "TAG_Float",
            self::DOUBLE => "TAG_Double",
            self::BYTE_ARRAY => "TAG_Byte_Array",
            self::STRING => "TAG_String",
            self::LIST => "TAG_List",
            self::COMPOUND => "TAG_Compound",
            self::INT_ARRAY => "TAG_Int_Array",
            self::LONG_ARRAY => "TAG_Long_Array",
        };
    }

    /**
     * @return class-string<Tag>
     */
    public function getTagClass(): string
    {
        return match ($this) {
            self::END => EndTag::class,
            self::BYTE => ByteTag::class,
            self::SHORT => ShortTag::class,
            self::INT => IntTag::class,
            self::LONG => LongTag::class,
            self::FLOAT => FloatTag::class,
            self::DOUBLE => DoubleTag::class,
            self::BYTE_ARRAY => ByteArrayTag::class,
            self::STRING => StringTag::class,
            self::LIST => ListTag::class,
            self::COMPOUND => CompoundTag::class,
            self::INT_ARRAY => IntArrayTag::class,
            self::LONG_ARRAY => LongArrayTag::class,
        };
    }

    /**
     * Create an empty tag of this type
     */
    public function createTag(?TagOptions $options = null): Tag
    {
        $class = $this->getTagClass();
        return new $class($options);
    }
}
