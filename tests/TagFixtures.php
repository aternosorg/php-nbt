<?php

namespace Aternos\Nbt\Tests;

use Aternos\Nbt\Tag\ByteArrayTag;
use Aternos\Nbt\Tag\ByteTag;
use Aternos\Nbt\Tag\CompoundTag;
use Aternos\Nbt\Tag\DoubleTag;
use Aternos\Nbt\Tag\FloatTag;
use Aternos\Nbt\Tag\IntArrayTag;
use Aternos\Nbt\Tag\IntTag;
use Aternos\Nbt\Tag\ListTag;
use Aternos\Nbt\Tag\LongArrayTag;
use Aternos\Nbt\Tag\LongTag;
use Aternos\Nbt\Tag\ShortTag;
use Aternos\Nbt\Tag\StringTag;

/**
 * Builds NBT structures that contain every tag type and the edge cases of each format
 */
class TagFixtures
{
    /**
     * A root compound tag containing every tag type, including nested and empty containers
     */
    public static function everyTagType(): CompoundTag
    {
        $root = new CompoundTag();
        $root->set("byte", new ByteTag()->setValue(-128));
        $root->set("short", new ShortTag()->setValue(-32768));
        $root->set("int", new IntTag()->setValue(-2147483648));
        $root->set("long", new LongTag()->setValue(PHP_INT_MIN));
        $root->set("longMax", new LongTag()->setValue(PHP_INT_MAX));
        $root->set("float", new FloatTag()->setValue(-1.5));
        $root->set("double", new DoubleTag()->setValue(0.1));
        $root->set("string", new StringTag()->setValue("Hello, Wörld! \u{1F600} with NUL \0 byte"));
        $root->set("emptyString", new StringTag()->setValue(""));

        $byteArray = new ByteArrayTag();
        foreach ([0, 1, -1, 127, -128] as $value) {
            $byteArray[] = $value;
        }
        $root->set("byteArray", $byteArray);

        $intArray = new IntArrayTag();
        foreach ([0, 1, -1, 2147483647, -2147483648] as $value) {
            $intArray[] = $value;
        }
        $root->set("intArray", $intArray);

        $longArray = new LongArrayTag();
        foreach ([0, 1, -1, PHP_INT_MAX, PHP_INT_MIN] as $value) {
            $longArray[] = $value;
        }
        $root->set("longArray", $longArray);

        $stringList = new ListTag();
        $stringList[] = new StringTag()->setValue("first");
        $stringList[] = new StringTag()->setValue("second");
        $root->set("stringList", $stringList);

        $root->set("emptyList", new ListTag());

        $compoundList = new ListTag();
        $compoundList[] = new CompoundTag()->set("id", new IntTag()->setValue(1));
        $compoundList[] = new CompoundTag()->set("id", new IntTag()->setValue(2));
        $root->set("compoundList", $compoundList);

        $nestedList = new ListTag();
        $nestedList[] = self::intList(1, 2);
        $nestedList[] = self::intList(3);
        $root->set("nestedList", $nestedList);

        $root->set("emptyCompound", new CompoundTag());
        $root->set("nested", new CompoundTag()
            ->set("inner", new CompoundTag()
                ->set("value", new StringTag()->setValue("deep"))));

        return $root;
    }

    /**
     * @param int ...$values
     */
    public static function intList(int ...$values): ListTag
    {
        $list = new ListTag();
        foreach ($values as $value) {
            $list[] = new IntTag()->setValue($value);
        }
        return $list;
    }
}
