<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tests;

use Aternos\Nbt\Exception\RawTagModificationException;
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
     * A root compound with a "raw" compound that contains every tag type except long arrays,
     * and a "parsed" compound next to it
     *
     * @throws Exception
     */
    protected function createData(NbtFormat $format): string
    {
        $raw = TagFixtures::everyTagType();
        $raw->delete("longArray");

        $root = new CompoundTag()
            ->set("raw", $raw)
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
    protected function load(string $data, NbtFormat $format, TagOptions $options): CompoundTag
    {
        $tag = Tag::load(new StringReader($data, $format), $options);
        $this->assertInstanceOf(CompoundTag::class, $tag);
        return $tag;
    }

    /**
     * @throws Exception
     */
    protected function write(Tag $tag, NbtFormat $format): string
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
        foreach ([NbtFormat::JavaEdition, NbtFormat::BedrockEdition, NbtFormat::BedrockEditionNetwork] as $format) {
            $data = $this->createData($format);
            $options = new TagOptions()->setRawCompoundPaths(["/raw"]);
            $root = $this->load($data, $format, $options);

            $this->assertTrue($root->getCompound("raw")?->isRaw());
            $this->assertFalse($root->getCompound("parsed")?->isRaw());
            $this->assertFalse($root->isRaw());
            $this->assertSame($data, $this->write($root, $format));
        }
    }

    /**
     * @throws Exception
     */
    public function testParsedCompoundPathsReadEverythingElseRaw(): void
    {
        $data = $this->createData(NbtFormat::JavaEdition);
        $options = new TagOptions()->setParsedCompoundPaths(["/parsed"]);
        $root = $this->load($data, NbtFormat::JavaEdition, $options);

        $this->assertTrue($root->getCompound("raw")?->isRaw());
        $this->assertFalse($root->getCompound("parsed")?->isRaw());
        $this->assertSame(42, $root->getCompound("parsed")->getInt("value")?->getValue());
        $this->assertSame($data, $this->write($root, NbtFormat::JavaEdition));
    }

    /**
     * @throws Exception
     */
    public function testListsAboveMaxLengthAreKeptAsRawBytes(): void
    {
        foreach ([NbtFormat::JavaEdition, NbtFormat::BedrockEdition, NbtFormat::BedrockEditionNetwork] as $format) {
            $data = $this->createData($format);
            $options = new TagOptions()->setMaxListTagLength(3);
            $root = $this->load($data, $format, $options);

            $this->assertTrue($root->getList("longList")?->isRaw());
            $this->assertFalse($root->getList("shortList")?->isRaw());
            $this->assertSame($data, $this->write($root, $format));
        }
    }

    /**
     * @throws Exception
     */
    public function testRawCompoundCannotBeModified(): void
    {
        $data = $this->createData(NbtFormat::JavaEdition);
        $root = $this->load($data, NbtFormat::JavaEdition, new TagOptions()->setRawCompoundPaths(["/raw"]));

        $this->expectException(RawTagModificationException::class);
        $root->getCompound("raw")?->set("new", new StringTag()->setValue("value"));
    }

    /**
     * @throws Exception
     */
    public function testRawCompoundCannotChangeFormat(): void
    {
        $data = $this->createData(NbtFormat::JavaEdition);
        $root = $this->load($data, NbtFormat::JavaEdition, new TagOptions()->setRawCompoundPaths(["/raw"]));

        $this->expectException(RawTagModificationException::class);
        $this->write($root, NbtFormat::BedrockEdition);
    }

    /**
     * @throws Exception
     */
    public function testRawListCannotBeModified(): void
    {
        $data = $this->createData(NbtFormat::JavaEdition);
        $root = $this->load($data, NbtFormat::JavaEdition, new TagOptions()->setMaxListTagLength(3));

        $list = $root->getList("longList");
        $this->assertNotNull($list);
        $this->expectException(RawTagModificationException::class);
        $list[] = new IntTag()->setValue(6);
    }

    /**
     * @throws Exception
     */
    public function testRawListCannotChangeFormat(): void
    {
        $data = $this->createData(NbtFormat::JavaEdition);
        $root = $this->load($data, NbtFormat::JavaEdition, new TagOptions()->setMaxListTagLength(3));

        $this->expectException(RawTagModificationException::class);
        $this->write($root, NbtFormat::BedrockEdition);
    }

    /**
     * @throws Exception
     */
    public function testRawListKeepsContentType(): void
    {
        $data = $this->createData(NbtFormat::JavaEdition);
        $root = $this->load($data, NbtFormat::JavaEdition, new TagOptions()->setMaxListTagLength(3));

        $this->assertInstanceOf(ListTag::class, $root->getList("longList"));
        $this->assertSame(IntTag::TYPE, $root->getList("longList")->getContentType());
    }
}
