<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tests;

use Aternos\Nbt\Exception\InvalidTagValueException;
use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\Tag\ArrayValueTag;
use Aternos\Nbt\Tag\ByteArrayTag;
use Aternos\Nbt\Tag\ByteTag;
use Aternos\Nbt\Tag\CompoundTag;
use Aternos\Nbt\Tag\DoubleTag;
use Aternos\Nbt\Tag\EndTag;
use Aternos\Nbt\Tag\FloatTag;
use Aternos\Nbt\Tag\IntArrayTag;
use Aternos\Nbt\Tag\IntTag;
use Aternos\Nbt\Tag\ListTag;
use Aternos\Nbt\Tag\LongArrayTag;
use Aternos\Nbt\Tag\LongTag;
use Aternos\Nbt\Tag\ShortTag;
use Aternos\Nbt\Tag\StringTag;
use Aternos\Nbt\Tag\Tag;
use Aternos\Nbt\Tag\TagType;
use Aternos\Nbt\IO\Reader\StringReader;
use Aternos\Nbt\IO\Writer\StringWriter;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TagManipulationTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testCompoundSetGetAndDelete(): void
    {
        $compound = new CompoundTag();
        $int = new IntTag()->setValue(1);
        $compound["a"] = $int;
        $compound->set("b", new StringTag()->setValue("b"));

        $this->assertCount(2, $compound);
        $this->assertSame($int, $compound["a"]);
        $this->assertSame("a", $int->getName());
        $this->assertSame($compound, $int->getParentTag());
        $this->assertTrue(isset($compound["a"]));
        $this->assertNull($compound["missing"]);
        $this->assertNull($compound->getString("a"), "typed getter returns null for a different tag type");
        $this->assertSame("b", $compound->getString("b")->getValue());

        $compound->delete("a");
        $this->assertCount(1, $compound);
        $this->assertFalse(isset($compound["a"]));
        $this->assertNull($int->getParentTag());
    }

    /**
     * @throws Exception
     */
    public function testCompoundReplacesExistingKey(): void
    {
        $compound = new CompoundTag();
        $compound["a"] = new IntTag()->setValue(1);
        $compound["b"] = new IntTag()->setValue(2);
        $compound["a"] = new IntTag()->setValue(3);

        $this->assertCount(2, $compound);
        $this->assertSame(3, $compound->getInt("a")->getValue());
    }

    /**
     * @throws Exception
     */
    public function testCompoundMovesChildToNewKey(): void
    {
        $compound = new CompoundTag();
        $compound["a"] = new IntTag()->setValue(1);
        $compound["b"] = new IntTag()->setValue(2);
        $moved = $compound["a"];
        $compound["c"] = $moved;

        $this->assertCount(2, $compound);
        $this->assertFalse(isset($compound["a"]));
        $this->assertSame($moved, $compound["c"]);
        $this->assertSame($compound, $moved->getParentTag());
        $this->assertSame(["b", "c"], array_keys(iterator_to_array($compound)));
    }

    /**
     * @throws Exception
     */
    public function testCompoundMovesChildToExistingKey(): void
    {
        $compound = new CompoundTag();
        $compound["a"] = new IntTag()->setValue(1);
        $replaced = new IntTag()->setValue(2);
        $compound["b"] = $replaced;
        $moved = $compound["a"];
        $compound["b"] = $moved;

        $this->assertCount(1, $compound);
        $this->assertSame($moved, $compound["b"]);
        $this->assertSame($compound, $moved->getParentTag());
        $this->assertNull($replaced->getParentTag());

        $writer = new StringWriter()->setFormat(NbtFormat::JAVA_EDITION);
        $compound->write($writer);
        $this->assertNotSame("", $writer->getStringData());
    }

    /**
     * @throws Exception
     */
    public function testCompoundAppendUsesTagName(): void
    {
        $compound = new CompoundTag();
        $compound[] = new IntTag()->setName("named")->setValue(1);
        $this->assertSame(1, $compound->getInt("named")->getValue());
    }

    public function testCompoundRejectsUnnamedAppend(): void
    {
        $this->expectException(Exception::class);
        $compound = new CompoundTag();
        $compound[] = new IntTag();
    }

    public function testCompoundRejectsNonTagValues(): void
    {
        $this->expectException(Exception::class);
        $compound = new CompoundTag();
        $compound["a"] = 1;
    }

    /**
     * @throws Exception
     */
    public function testCompoundWithDuplicateNamesCannotBeWritten(): void
    {
        $renamed = new IntTag()->setValue(2);
        $root = new CompoundTag()
            ->set("a", new IntTag()->setValue(1))
            ->set("b", $renamed);
        $renamed->setName("a");

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Duplicate key 'a'");
        $root->write(new StringWriter()->setFormat(NbtFormat::JAVA_EDITION));
    }

    public function testCompoundIteratesInInsertionOrderWithNamesAsKeys(): void
    {
        $compound = new CompoundTag()
            ->set("b", new IntTag()->setValue(1))
            ->set("a", new IntTag()->setValue(2));

        $values = [];
        foreach ($compound as $name => $tag) {
            $values[$name] = $tag->getValue();
        }
        $this->assertSame(["b" => 1, "a" => 2], $values);
    }

    /**
     * @throws Exception
     */
    public function testListInfersContentTypeFromFirstValue(): void
    {
        $list = new ListTag();
        $this->assertSame(TagType::END, $list->getContentType());

        $list[] = new IntTag()->setValue(1);
        $this->assertSame(TagType::INT, $list->getContentType());
        $this->assertSame($list, $list[0]->getParentTag());
    }

    public function testListRejectsDifferentContentType(): void
    {
        $list = TagFixtures::intList(1);
        $this->expectException(Exception::class);
        $list[] = new StringTag();
    }

    public function testListRejectsNonTagValues(): void
    {
        $list = new ListTag();
        $this->expectException(Exception::class);
        $list[] = 1;
    }

    public function testListContentTypeCannotChangeWhileFilled(): void
    {
        $list = TagFixtures::intList(1);
        $this->expectException(Exception::class);
        $list->setContentType(TagType::STRING);
    }

    public function testListReplaceAndUnsetUpdateParents(): void
    {
        $list = TagFixtures::intList(1, 2);
        $old = $list[0];
        $new = new IntTag()->setValue(3);
        $list[0] = $new;

        $this->assertNull($old->getParentTag());
        $this->assertSame($list, $new->getParentTag());

        unset($list[0]);
        $this->assertNull($new->getParentTag());
        $this->assertCount(1, $list);
        $this->assertSame(2, $list[0]->getValue(), "entries after an unset entry move down");
    }

    public function testListRejectsOffsetsThatWouldLeaveGaps(): void
    {
        $list = TagFixtures::intList(1);
        $this->expectExceptionObject(new InvalidTagValueException("Invalid offset 5 for TAG_List with 1 entries"));
        $list[5] = new IntTag();
    }

    public function testListRejectsDifferentContentTypeWithMessage(): void
    {
        $list = TagFixtures::intList(1);
        $this->expectExceptionObject(new InvalidTagValueException("Cannot add TAG_String to TAG_List with content type TAG_Int"));
        $list[] = new StringTag();
    }

    public function testListAppendsAtOffsetEqualToCount(): void
    {
        $list = TagFixtures::intList(1);
        $list[1] = new IntTag()->setValue(2);
        $this->assertCount(2, $list);
        $this->assertSame(2, $list[1]->getValue());
    }

    public function testListIteration(): void
    {
        $values = [];
        foreach (TagFixtures::intList(1, 2, 3) as $tag) {
            $values[] = $tag->getValue();
        }
        $this->assertSame([1, 2, 3], $values);
    }

    public function testByteArrayIterationIncludesZero(): void
    {
        $array = new ByteArrayTag();
        $array[] = 0;
        $array[] = 1;

        $values = [];
        foreach ($array as $value) {
            $values[] = $value;
        }
        $this->assertSame([0, 1], $values);
    }

    public function testByteArrayStringShowsBytesAsTwoHexDigits(): void
    {
        $array = new ByteArrayTag()->setName("bytes");
        foreach ([0, 1, -1, 127, -128] as $value) {
            $array[] = $value;
        }
        $this->assertSame("TAG_Byte_Array('bytes'): 5 bytes [00 01 ff 7f 80]", strval($array));
    }

    /**
     * @throws Exception
     */
    public function testSetValuesKeepsIndicesContinuous(): void
    {
        $array = new ByteArrayTag()->setValues([3 => 1, 7 => 2]);
        $array[] = 5;

        $this->assertSame([1, 2, 5], $array->getValues());
        $this->assertSame(1, $array[0]);
    }

    public function testSetValuesRejectsValuesThatAreNoInts(): void
    {
        $this->expectExceptionObject(new InvalidTagValueException("Invalid value of type string for TAG_Byte_Array"));
        new ByteArrayTag()->setValues(["1"]);
    }

    public function testSetValuesRejectsValuesOutOfRangeAndKeepsOldValues(): void
    {
        $array = new IntArrayTag()->setValues([1]);
        try {
            $array->setValues([0, 0x80000000]);
            $this->fail("Expected an InvalidTagValueException");
        } catch (InvalidTagValueException $exception) {
            $this->assertSame("Value 2147483648 is out of range for TAG_Int_Array (-2147483648 to 2147483647)", $exception->getMessage());
        }
        $this->assertSame([1], $array->getValues());
    }

    public function testArrayGetReturnsNullForMissingOffset(): void
    {
        $array = new IntArrayTag()->setValues([1]);
        $this->assertNull($array[1]);
        $this->assertNull($array["a"]);
        $this->assertFalse(isset($array[1]));
    }

    public function testArrayRejectsNegativeOffset(): void
    {
        $array = new IntArrayTag()->setValues([1]);
        $this->expectExceptionObject(new InvalidTagValueException("Invalid offset -1 for TAG_Int_Array with 1 entries"));
        $array[-1] = 2;
    }

    /**
     * @throws Exception
     */
    public function testLongArrayIsWrittenWithValuesSetAfterReading(): void
    {
        $original = new CompoundTag()->set("longs", new LongArrayTag()->setValues([1, 2, 3]));
        $writer = new StringWriter();
        $original->write($writer);

        /** @var CompoundTag $read */
        $read = Tag::load(new StringReader($writer->getStringData(), NbtFormat::JAVA_EDITION));
        $longs = $read->getLongArray("longs");
        $longs->setValues([2, 3, 4]);

        $writer = new StringWriter();
        $read->write($writer);
        /** @var CompoundTag $reread */
        $reread = Tag::load(new StringReader($writer->getStringData(), NbtFormat::JAVA_EDITION));
        $this->assertSame([2, 3, 4], $reread->getLongArray("longs")->getValues());
    }

    public function testArrayRejectsInvalidValue(): void
    {
        $array = new ByteArrayTag();
        $this->expectException(Exception::class);
        $array[] = "a";
    }

    /**
     * @throws Exception
     */
    public function testEqualsComparesTypeAndValue(): void
    {
        $this->assertTrue(new IntTag()->setValue(1)->equals(new IntTag()->setValue(1)));
        $this->assertFalse(new IntTag()->setValue(1)->equals(new IntTag()->setValue(2)));
        $this->assertFalse(new IntTag()->setValue(1)->equals(new StringTag()->setValue("1")));
        $this->assertFalse(TagFixtures::intList(1)->equals(TagFixtures::intList(1, 2)));
        $this->assertTrue(TagFixtures::everyTagType()->equals(TagFixtures::everyTagType()));
    }

    /**
     * @throws Exception
     */
    public function testPath(): void
    {
        $inner = new IntTag()->setValue(1);
        $root = new CompoundTag()->setName("")
            ->set("a", new CompoundTag()->set("b", $inner));

        $this->assertSame(["", "a", "b"], $inner->getPath());
        $this->assertSame("/a/b", $inner->getStringPath());
        $this->assertSame("", $root->getStringPath());
    }

    public function testJsonSerialize(): void
    {
        $compound = new CompoundTag()
            ->set("int", new IntTag()->setValue(1))
            ->set("list", TagFixtures::intList(1, 2));
        $this->assertSame('{"int":1,"list":[1,2]}', json_encode($compound));
    }

    /**
     * @return array<string, array{string, Tag}>
     */
    public static function typedGetterProvider(): array
    {
        return [
            "byte" => ["getByte", new ByteTag()->setValue(1)],
            "short" => ["getShort", new ShortTag()->setValue(1)],
            "long" => ["getLong", new LongTag()->setValue(1)],
            "float" => ["getFloat", new FloatTag()->setValue(1.5)],
            "double" => ["getDouble", new DoubleTag()->setValue(1.5)],
            "byte array" => ["getByteArray", new ByteArrayTag()],
            "int array" => ["getIntArray", new IntArrayTag()],
            "long array" => ["getLongArray", new LongArrayTag()],
            "list" => ["getList", new ListTag()],
            "compound" => ["getCompound", new CompoundTag()],
        ];
    }

    /**
     * @throws Exception
     */
    #[DataProvider("typedGetterProvider")]
    public function testTypedGetterReturnsOnlyMatchingTagType(string $getter, Tag $tag): void
    {
        $compound = new CompoundTag()
            ->set("match", $tag)
            ->set("other", new StringTag()->setValue("other"));

        $this->assertSame($tag, $compound->$getter("match"));
        $this->assertNull($compound->$getter("other"));
        $this->assertNull($compound->$getter("missing"));
    }

    /**
     * @throws Exception
     */
    public function testGetListFiltersByContentType(): void
    {
        $compound = new CompoundTag()->set("list", TagFixtures::intList(1));

        $this->assertNotNull($compound->getList("list", TagType::INT));
        $this->assertNull($compound->getList("list", TagType::STRING));
    }

    /**
     * @throws Exception
     */
    public function testEqualsOnContainers(): void
    {
        $compound = TagFixtures::everyTagType();
        $this->assertTrue($compound->equals($compound));
        $this->assertFalse($compound->equals(TagFixtures::intList(1)));

        $otherValue = TagFixtures::everyTagType()->set("int", new IntTag()->setValue(0));
        $this->assertFalse($compound->equals($otherValue));

        $otherKey = TagFixtures::everyTagType();
        $otherKey->delete("int");
        $otherKey->set("renamedInt", new IntTag()->setValue(-2147483648));
        $this->assertFalse($compound->equals($otherKey));

        $list = TagFixtures::intList(1, 2);
        $this->assertTrue($list->equals($list));
        $this->assertFalse($list->equals(TagFixtures::intList(1, 3)));

        $array = $this->createByteArray(1, 2);
        $this->assertTrue($array->equals($array));
        $this->assertTrue($array->equals($this->createByteArray(1, 2)));
        $this->assertFalse($array->equals($this->createByteArray(1, 3)));
        $this->assertFalse($array->equals($this->createByteArray(1)));
        $this->assertFalse($array->equals(new IntArrayTag()));
    }

    public function testEndTag(): void
    {
        $tag = new EndTag();

        $this->assertTrue($tag->equals(new EndTag()));
        $this->assertFalse($tag->equals(new IntTag()));
        $this->assertSame("", $tag->toSNBT());
        $this->assertNull($tag->jsonSerialize());
        $this->assertSame("TAG_End('None'): ", (string)$tag);
    }

    public function testCompoundRejectsNonStringKeys(): void
    {
        $compound = new CompoundTag();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Invalid CompoundTag key");
        $compound[1] = new IntTag();
    }

    /**
     * @return array<string, array{ArrayValueTag}>
     */
    public static function arrayTagProvider(): array
    {
        return [
            "byte array" => [new ByteArrayTag()],
            "int array" => [new IntArrayTag()],
            "long array" => [new LongArrayTag()],
        ];
    }

    #[DataProvider("arrayTagProvider")]
    public function testArrayRejectsNonIntegerKeys(ArrayValueTag $array): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Invalid offset 'a' for " . $array->getType()->getName() . " with 0 entries");
        $array["a"] = 1;
    }

    #[DataProvider("arrayTagProvider")]
    public function testArraySetIssetAndUnsetByOffset(ArrayValueTag $array): void
    {
        $array[] = 1;
        $array[] = 2;
        $array[0] = 3;

        $this->assertTrue(isset($array[0]));
        $this->assertSame(3, $array[0]);

        unset($array[0]);
        $this->assertCount(1, $array);
        $this->assertSame(2, $array[0], "entries after an unset entry move down");
        $this->assertFalse(isset($array[1]));
    }

    /**
     * @throws Exception
     */
    public function testEmptyListContentTypeCanBeChanged(): void
    {
        $list = new ListTag()->setContentType(TagType::STRING);
        $this->assertSame(TagType::STRING, $list->getContentType());
    }

    public function testValueGetters(): void
    {
        $string = new StringTag()->setValue("äb");
        $this->assertSame(3, $string->getLength(), "length is counted in bytes");
        $this->assertSame('"äb"', json_encode($string, JSON_UNESCAPED_UNICODE));
        $this->assertSame("1.5", json_encode(new FloatTag()->setValue(1.5)));

        $long = new LongTag()->setValue(1);
        $this->assertNull($long->getRawValue());
        $long->setRawValue("raw");
        $this->assertSame("raw", $long->getRawValue());
    }

    public function testByteArrayStringIsTruncatedAfter32Values(): void
    {
        $array = $this->createByteArray(...array_fill(0, 33, 0x1f));
        $this->assertStringEndsWith("1f ...]", (string)$array);
    }

    /**
     * @throws Exception
     */
    public function testCircularStructureIsDetected(): void
    {
        $outer = new CompoundTag();
        $inner = new CompoundTag();
        $outer->set("inner", $inner);
        $inner->set("outer", $outer);

        $this->assertNull($inner->getStringPath());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Circular NBT structure detected");
        $outer->write(new StringWriter());
    }

    protected function createByteArray(int ...$values): ByteArrayTag
    {
        $array = new ByteArrayTag();
        foreach ($values as $value) {
            $array[] = $value;
        }
        return $array;
    }
}
