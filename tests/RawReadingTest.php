<?php

namespace Aternos\Nbt\Tests;

use Aternos\Nbt\IO\Reader\StringReader;
use Aternos\Nbt\IO\Writer\StringWriter;
use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\Tag\CompoundTag;
use Aternos\Nbt\Tag\IntTag;
use Aternos\Nbt\Tag\ListTag;
use Aternos\Nbt\Tag\StringTag;
use Aternos\Nbt\Tag\Tag;
use Aternos\Nbt\Tag\TagOptions;
use Exception;
use PHPUnit\Framework\TestCase;

class RawReadingTest extends TestCase
{
    /**
     * A root compound with a "raw" compound that contains every tag type,
     * and a "parsed" compound next to it
     *
     * @throws Exception
     */
    protected function createData(int $format): string
    {
        $root = new CompoundTag()
            ->set("raw", TagFixtures::everyTagType())
            ->set("parsed", new CompoundTag()->set("value", new IntTag()->setValue(42)))
            ->set("longList", TagFixtures::intList(1, 2, 3, 4, 5))
            ->set("shortList", TagFixtures::intList(1));

        $writer = new StringWriter()->setFormat($format);
        $root->write($writer);
        return $writer->getStringData();
    }

    /**
     * @throws Exception
     */
    protected function load(string $data, int $format, TagOptions $options): CompoundTag
    {
        $tag = Tag::load(new StringReader($data, $format), $options);
        $this->assertInstanceOf(CompoundTag::class, $tag);
        return $tag;
    }

    /**
     * @throws Exception
     */
    protected function write(Tag $tag, int $format): string
    {
        $writer = new StringWriter()->setFormat($format);
        $tag->write($writer);
        return $writer->getStringData();
    }

    /**
     * @throws Exception
     */
    public function testRawCompoundPathsAreKeptAsRawBytes(): void
    {
        foreach ([NbtFormat::JAVA_EDITION, NbtFormat::BEDROCK_EDITION, NbtFormat::BEDROCK_EDITION_NETWORK] as $format) {
            $data = $this->createData($format);
            $options = new TagOptions()->setRawCompoundPaths(["/raw"]);
            $root = $this->load($data, $format, $options);

            $this->assertTrue($root->getCompound("raw")->isRaw());
            $this->assertFalse($root->getCompound("parsed")->isRaw());
            $this->assertFalse($root->isRaw());
            $this->assertSame($data, $this->write($root, $format));
        }
    }

    /**
     * @throws Exception
     */
    public function testParsedCompoundPathsReadEverythingElseRaw(): void
    {
        $data = $this->createData(NbtFormat::JAVA_EDITION);
        $options = new TagOptions()->setParsedCompoundPaths(["/parsed"]);
        $root = $this->load($data, NbtFormat::JAVA_EDITION, $options);

        $this->assertTrue($root->getCompound("raw")->isRaw());
        $this->assertFalse($root->getCompound("parsed")->isRaw());
        $this->assertSame(42, $root->getCompound("parsed")->getInt("value")->getValue());
        $this->assertSame($data, $this->write($root, NbtFormat::JAVA_EDITION));
    }

    /**
     * @throws Exception
     */
    public function testListsAboveMaxLengthAreKeptAsRawBytes(): void
    {
        foreach ([NbtFormat::JAVA_EDITION, NbtFormat::BEDROCK_EDITION, NbtFormat::BEDROCK_EDITION_NETWORK] as $format) {
            $data = $this->createData($format);
            $options = new TagOptions()->setMaxListTagLength(3);
            $root = $this->load($data, $format, $options);

            $this->assertTrue($root->getList("longList")->isRaw());
            $this->assertFalse($root->getList("shortList")->isRaw());
            $this->assertSame($data, $this->write($root, $format));
        }
    }

    /**
     * @throws Exception
     */
    public function testRawCompoundCannotBeModified(): void
    {
        $data = $this->createData(NbtFormat::JAVA_EDITION);
        $root = $this->load($data, NbtFormat::JAVA_EDITION, new TagOptions()->setRawCompoundPaths(["/raw"]));

        $this->expectException(Exception::class);
        $root->getCompound("raw")->set("new", new StringTag()->setValue("value"));
    }

    /**
     * @throws Exception
     */
    public function testRawCompoundCannotChangeFormat(): void
    {
        $data = $this->createData(NbtFormat::JAVA_EDITION);
        $root = $this->load($data, NbtFormat::JAVA_EDITION, new TagOptions()->setRawCompoundPaths(["/raw"]));

        $this->expectException(Exception::class);
        $this->write($root, NbtFormat::BEDROCK_EDITION);
    }

    /**
     * @throws Exception
     */
    public function testRawListCannotBeModified(): void
    {
        $data = $this->createData(NbtFormat::JAVA_EDITION);
        $root = $this->load($data, NbtFormat::JAVA_EDITION, new TagOptions()->setMaxListTagLength(3));

        $this->expectException(Exception::class);
        $root->getList("longList")[] = new IntTag()->setValue(6);
    }

    /**
     * @throws Exception
     */
    public function testRawListCannotChangeFormat(): void
    {
        $data = $this->createData(NbtFormat::JAVA_EDITION);
        $root = $this->load($data, NbtFormat::JAVA_EDITION, new TagOptions()->setMaxListTagLength(3));

        $this->expectException(Exception::class);
        $this->write($root, NbtFormat::BEDROCK_EDITION);
    }

    /**
     * @throws Exception
     */
    public function testRawListKeepsContentType(): void
    {
        $data = $this->createData(NbtFormat::JAVA_EDITION);
        $root = $this->load($data, NbtFormat::JAVA_EDITION, new TagOptions()->setMaxListTagLength(3));

        $this->assertInstanceOf(ListTag::class, $root->getList("longList"));
        $this->assertSame(IntTag::TYPE, $root->getList("longList")->getContentTag());
    }

    /**
     * @throws Exception
     */
    public function testRawCompoundEntriesCannotBeRemoved(): void
    {
        $data = $this->createData(NbtFormat::JAVA_EDITION);
        $root = $this->load($data, NbtFormat::JAVA_EDITION, new TagOptions()->setRawCompoundPaths(["/raw"]));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Raw compound tags cannot be modified");
        $root->getCompound("raw")->delete("int");
    }

    /**
     * @throws Exception
     */
    public function testRawListEntriesCannotBeRemoved(): void
    {
        $data = $this->createData(NbtFormat::JAVA_EDITION);
        $root = $this->load($data, NbtFormat::JAVA_EDITION, new TagOptions()->setMaxListTagLength(3));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Raw list tags cannot be modified");
        unset($root->getList("longList")[0]);
    }

    /**
     * @throws Exception
     */
    public function testRawListContentTypeCannotBeChanged(): void
    {
        $data = $this->createData(NbtFormat::JAVA_EDITION);
        $root = $this->load($data, NbtFormat::JAVA_EDITION, new TagOptions()->setMaxListTagLength(3));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Raw list tags cannot be modified");
        $root->getList("longList")->setContentTag(IntTag::TYPE);
    }

    /**
     * @throws Exception
     */
    public function testRawTagsShowTheirSizeInBytes(): void
    {
        $data = $this->createData(NbtFormat::JAVA_EDITION);
        $options = new TagOptions()->setRawCompoundPaths(["/raw"])->setMaxListTagLength(3);
        $root = $this->load($data, NbtFormat::JAVA_EDITION, $options);

        // 5 ints, the length prefix is not part of the raw content
        $this->assertSame("TAG_List<TAG_Int>('longList'): 20 bytes", (string)$root->getList("longList"));
        // e.g. "TAG_Compound('raw'): 213 bytes"
        $this->assertMatchesRegularExpression("/^TAG_Compound\('raw'\): \d+ bytes$/", (string)$root->getCompound("raw"));
    }
}
