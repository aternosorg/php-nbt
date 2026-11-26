<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

/**
 * NBT tag types, backed by the type byte used in serialized NBT data
 */
enum TagType: int
{
    case End = 0;
    case Byte = 1;
    case Short = 2;
    case Int = 3;
    case Long = 4;
    case Float = 5;
    case Double = 6;
    case ByteArray = 7;
    case String = 8;
    case List = 9;
    case Compound = 10;
    case IntArray = 11;
    case LongArray = 12;

    /**
     * Name of the tag type as used in the NBT specification, e.g. "TAG_Byte_Array"
     */
    public function getName(): string
    {
        return match ($this) {
            self::End => "TAG_End",
            self::Byte => "TAG_Byte",
            self::Short => "TAG_Short",
            self::Int => "TAG_Int",
            self::Long => "TAG_Long",
            self::Float => "TAG_Float",
            self::Double => "TAG_Double",
            self::ByteArray => "TAG_Byte_Array",
            self::String => "TAG_String",
            self::List => "TAG_List",
            self::Compound => "TAG_Compound",
            self::IntArray => "TAG_Int_Array",
            self::LongArray => "TAG_Long_Array",
        };
    }

    /**
     * @return class-string<Tag>
     */
    public function getTagClass(): string
    {
        return match ($this) {
            self::End => EndTag::class,
            self::Byte => ByteTag::class,
            self::Short => ShortTag::class,
            self::Int => IntTag::class,
            self::Long => LongTag::class,
            self::Float => FloatTag::class,
            self::Double => DoubleTag::class,
            self::ByteArray => ByteArrayTag::class,
            self::String => StringTag::class,
            self::List => ListTag::class,
            self::Compound => CompoundTag::class,
            self::IntArray => IntArrayTag::class,
            self::LongArray => LongArrayTag::class,
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
