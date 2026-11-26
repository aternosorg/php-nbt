<?php

namespace Aternos\Nbt\Tests;

use Aternos\Nbt\IO\Writer\StringWriter;
use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\Tag\ByteArrayTag;
use Aternos\Nbt\Tag\CompoundTag;
use Aternos\Nbt\Tag\IntTag;
use Aternos\Nbt\Tag\ListTag;
use Aternos\Nbt\Tag\StringTag;
use Aternos\Nbt\Tag\TagType;
use Exception;
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
        $this->assertSame(TagType::TAG_End, $list->getContentTag());

        $list[] = new IntTag()->setValue(1);
        $this->assertSame(TagType::TAG_Int, $list->getContentTag());
        $this->assertSame($list, $list[0]->getParentTag());
    }

    public function testListRejectsDifferentContentType(): void
    {
        $list = TagFixtures::intList(1);
        $this->expectException(Exception::class);
        $list[] = new StringTag();
    }

    public function testListContentTypeCannotChangeWhileFilled(): void
    {
        $list = TagFixtures::intList(1);
        $this->expectException(Exception::class);
        $list->setContentTag(TagType::TAG_String);
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
}
