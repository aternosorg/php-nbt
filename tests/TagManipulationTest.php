<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tests;

use Aternos\Nbt\Exception\CircularStructureException;
use Aternos\Nbt\Exception\InvalidTagValueException;
use Aternos\Nbt\Exception\NbtException;
use Aternos\Nbt\IO\Writer\StringWriter;
use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\Tag\ByteArrayTag;
use Aternos\Nbt\Tag\ByteTag;
use Aternos\Nbt\Tag\CompoundTag;
use Aternos\Nbt\Tag\IntArrayTag;
use Aternos\Nbt\Tag\IntTag;
use Aternos\Nbt\Tag\ListTag;
use Aternos\Nbt\Tag\ShortTag;
use Aternos\Nbt\Tag\StringTag;
use Aternos\Nbt\Tag\TagType;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class TagManipulationTest extends TestCase
{
    /**
     * @throws NbtException
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
        $this->assertSame("b", $compound->getString("b")?->getValue());

        $compound->delete("a");
        $this->assertCount(1, $compound);
        $this->assertFalse(isset($compound["a"]));
        $this->assertNull($int->getParentTag());
    }

    /**
     * @throws NbtException
     */
    public function testCompoundReplacesExistingKey(): void
    {
        $compound = new CompoundTag();
        $compound["a"] = new IntTag()->setValue(1);
        $compound["b"] = new IntTag()->setValue(2);
        $compound["a"] = new IntTag()->setValue(3);

        $this->assertCount(2, $compound);
        $this->assertSame(3, $compound->getInt("a")?->getValue());
    }

    /**
     * @throws NbtException
     */
    public function testCompoundMovesChildToNewKey(): void
    {
        $compound = new CompoundTag();
        $compound["a"] = new IntTag()->setValue(1);
        $compound["b"] = new IntTag()->setValue(2);
        $moved = $compound["a"];
        $compound["c"] = $moved;

        $this->assertCount(2, $compound);
        $this->assertNull($compound["a"]);
        $this->assertSame($moved, $compound["c"]);
        $this->assertSame(["b", "c"], array_keys(iterator_to_array($compound)));

        $writer = new StringWriter();
        $compound->write($writer);
        $this->assertNotSame("", $writer->getStringData());
    }

    /**
     * @throws NbtException
     */
    public function testCompoundAppendUsesTagName(): void
    {
        $compound = new CompoundTag();
        $compound[] = new IntTag()->setName("named")->setValue(1);
        $this->assertSame(1, $compound->getInt("named")?->getValue());
    }

    public function testCompoundRejectsUnnamedAppend(): void
    {
        $compound = new CompoundTag();
        $this->expectException(InvalidTagValueException::class);
        $compound[] = new IntTag();
    }

    public function testCompoundRejectsNonTagValues(): void
    {
        $compound = new CompoundTag();
        $this->expectException(InvalidTagValueException::class);
        // @phpstan-ignore offsetAssign.valueType (testing the runtime check)
        $compound["a"] = 1;
    }

    /**
     * @throws NbtException
     */
    public function testCompoundIteratesInInsertionOrderWithNamesAsKeys(): void
    {
        $compound = new CompoundTag()
            ->set("b", new IntTag()->setValue(1))
            ->set("a", new IntTag()->setValue(2))
            ->set("1", new IntTag()->setValue(3));

        $values = [];
        foreach ($compound as $name => $tag) {
            $this->assertIsString($name);
            $this->assertInstanceOf(IntTag::class, $tag);
            $values[] = [$name, $tag->getValue()];
        }
        $this->assertSame([["b", 1], ["a", 2], ["1", 3]], $values);
    }

    /**
     * @throws NbtException
     */
    public function testListInfersContentTypeFromFirstValue(): void
    {
        $list = new ListTag();
        $this->assertSame(TagType::END, $list->getContentType());

        $list[] = new IntTag()->setValue(1);
        $this->assertSame(TagType::INT, $list->getContentType());
        $this->assertSame($list, $list[0]?->getParentTag());
    }

    public function testListRejectsDifferentContentType(): void
    {
        $list = TagFixtures::intList(1);
        $this->expectException(InvalidTagValueException::class);
        $list[] = new StringTag();
    }

    public function testListContentTypeCannotChangeWhileFilled(): void
    {
        $list = TagFixtures::intList(1);
        $this->expectException(InvalidTagValueException::class);
        $list->setContentType(TagType::STRING);
    }

    /**
     * @throws NbtException
     */
    public function testListReplaceAndUnsetUpdateParentsAndKeepIndicesContinuous(): void
    {
        $list = TagFixtures::intList(1, 2);
        $old = $list[0];
        $new = new IntTag()->setValue(3);
        $list[0] = $new;

        $this->assertNull($old?->getParentTag());
        $this->assertSame($list, $new->getParentTag());

        unset($list[0]);
        $this->assertNull($new->getParentTag());
        $this->assertCount(1, $list);
        $this->assertSame(2, $list[0]?->jsonSerialize(), "entries after an unset entry move down");
    }

    public function testListRejectsOffsetsThatWouldLeaveGaps(): void
    {
        $list = TagFixtures::intList(1);
        $this->expectException(InvalidTagValueException::class);
        $list[5] = new IntTag();
    }

    public function testListIteration(): void
    {
        $values = [];
        foreach (TagFixtures::intList(1, 2, 3) as $tag) {
            $values[] = $tag->jsonSerialize();
        }
        $this->assertSame([1, 2, 3], $values);
    }

    /**
     * @throws NbtException
     */
    public function testByteArrayIterationIncludesZero(): void
    {
        $array = new ByteArrayTag()->setValues([0, 1]);

        $values = [];
        foreach ($array as $value) {
            $values[] = $value;
        }
        $this->assertSame([0, 1], $values);
        $this->assertSame([0, 1], $array->getValues());
    }

    public function testArrayRejectsInvalidValue(): void
    {
        $array = new ByteArrayTag();
        $this->expectException(InvalidTagValueException::class);
        // @phpstan-ignore offsetAssign.valueType (testing the runtime check)
        $array[] = "a";
    }

    public function testByteArrayRejectsValuesOutOfRange(): void
    {
        $array = new ByteArrayTag();
        $this->expectException(InvalidTagValueException::class);
        $array[] = 128;
    }

    /**
     * @throws NbtException
     */
    public function testSetValuesKeepsIndicesContinuous(): void
    {
        // @phpstan-ignore argument.type (testing the runtime handling of keys that are not a list)
        $array = new ByteArrayTag()->setValues([3 => 1, 7 => 2]);
        $array[] = 5;

        $this->assertSame([1, 2, 5], $array->getValues());
        $this->assertSame(1, $array[0]);
    }

    public function testSetValuesRejectsValuesThatAreNoInts(): void
    {
        $this->expectException(InvalidTagValueException::class);
        $this->expectExceptionMessage("Invalid value of type string for TAG_Byte_Array");
        // @phpstan-ignore argument.type (testing the runtime check)
        new ByteArrayTag()->setValues(["1"]);
    }

    public function testIntArraySetValuesRejectsValuesOutOfRange(): void
    {
        $this->expectException(InvalidTagValueException::class);
        new IntArrayTag()->setValues([0, 0x80000000]);
    }

    /**
     * @param class-string<ByteTag|ShortTag|IntTag> $class
     */
    #[TestWith([ByteTag::class, 128], "byte too large")]
    #[TestWith([ByteTag::class, -129], "byte too small")]
    #[TestWith([ShortTag::class, 0x8000], "short too large")]
    #[TestWith([IntTag::class, -0x80000001], "int too small")]
    public function testIntValueTagsRejectValuesOutOfRange(string $class, int $value): void
    {
        $this->expectException(InvalidTagValueException::class);
        new $class()->setValue($value);
    }

    /**
     * @throws NbtException
     */
    public function testEqualsComparesTypeAndValue(): void
    {
        $this->assertTrue(new IntTag()->setValue(1)->equals(new IntTag()->setValue(1)));
        $this->assertFalse(new IntTag()->setValue(1)->equals(new IntTag()->setValue(2)));
        $this->assertFalse(new IntTag()->setValue(1)->equals(new ShortTag()->setValue(1)));
        $this->assertFalse(new IntTag()->setValue(1)->equals(new StringTag()->setValue("1")));
        $this->assertFalse(TagFixtures::intList(1)->equals(TagFixtures::intList(1, 2)));
        $this->assertFalse(new ByteArrayTag()->setValues([1])->equals(new IntArrayTag()->setValues([1])));
        $this->assertTrue(TagFixtures::everyTagType()->equals(TagFixtures::everyTagType()));
    }

    /**
     * @throws NbtException
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

    /**
     * @throws NbtException
     */
    public function testCircularParentsHaveNoPath(): void
    {
        $a = new CompoundTag()->setName("a");
        $b = new CompoundTag()->setName("b");
        $a->setParentTag($b);
        $b->setParentTag($a);

        $this->assertNull($a->getStringPath());
        $this->expectException(CircularStructureException::class);
        $a->getPath();
    }

    /**
     * @throws NbtException
     */
    public function testCircularStructureCannotBeWritten(): void
    {
        $root = new CompoundTag();
        $child = new CompoundTag();
        $root->set("child", $child);
        $child->set("root", $root);

        $this->expectException(CircularStructureException::class);
        $root->write(new StringWriter());
    }

    /**
     * @throws NbtException
     */
    public function testListContainingItselfCannotBeWritten(): void
    {
        $list = new ListTag()->setContentType(TagType::LIST);
        $list[] = $list;
        $root = new CompoundTag()->set("list", $list);

        $this->expectException(CircularStructureException::class);
        $root->write(new StringWriter());
    }

    /**
     * @throws NbtException
     */
    public function testFailedWriteDoesNotBreakLaterWrites(): void
    {
        $root = new CompoundTag()->set("emptyList", new ListTag());
        $root->set("string", new StringTag()->setValue(str_repeat("a", 0x10000)));
        try {
            $root->write(new StringWriter());
            $this->fail("Writing a too long string must fail");
        } catch (InvalidTagValueException) {
        }

        $root->delete("string");
        $writer = new StringWriter();
        $root->write($writer);
        $this->assertNotSame("", $writer->getStringData());
    }

    public function testJavaStringLengthIsCheckedAfterEncoding(): void
    {
        // 0x8000 NUL characters take two bytes each in Java's modified UTF-8
        $root = new CompoundTag()->set("nul", new StringTag()->setValue(str_repeat("\0", 0x8000)));

        $writer = new StringWriter()->setFormat(NbtFormat::BEDROCK_EDITION);
        $root->write($writer);
        $this->assertNotSame("", $writer->getStringData());

        $this->expectException(InvalidTagValueException::class);
        $root->write(new StringWriter()->setFormat(NbtFormat::JAVA_EDITION));
    }

    /**
     * @throws NbtException
     */
    public function testJsonSerialize(): void
    {
        $compound = new CompoundTag()
            ->set("int", new IntTag()->setValue(1))
            ->set("list", TagFixtures::intList(1, 2));
        $this->assertSame('{"int":1,"list":[1,2]}', json_encode($compound));
    }
}
