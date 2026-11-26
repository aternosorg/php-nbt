<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tests;

use Aternos\Nbt\Exception\CircularStructureException;
use Aternos\Nbt\Exception\CompressionException;
use Aternos\Nbt\Exception\InvalidTagValueException;
use Aternos\Nbt\Exception\NbtException;
use Aternos\Nbt\Exception\RawTagModificationException;
use Aternos\Nbt\Exception\StringDataFormatException;
use Aternos\Nbt\Exception\UnknownTagTypeException;
use Aternos\Nbt\IO\BinaryDataException;
use Aternos\Nbt\IO\Reader\GZipCompressedStringReader;
use Aternos\Nbt\IO\Reader\StringReader;
use Aternos\Nbt\IO\Reader\ZLibCompressedStringReader;
use Aternos\Nbt\IO\Writer\StringWriter;
use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\String\JavaEncoding;
use Aternos\Nbt\String\StringDataFormatException as DeprecatedStringDataFormatException;
use Aternos\Nbt\Tag\CompoundTag;
use Aternos\Nbt\Tag\IntArrayTag;
use Aternos\Nbt\Tag\IntTag;
use Aternos\Nbt\Tag\ListTag;
use Aternos\Nbt\Tag\StringTag;
use Aternos\Nbt\Tag\Tag;
use Aternos\Nbt\Tag\TagOptions;
use Aternos\Nbt\Tag\TagType;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    /**
     * @param class-string $class
     */
    #[TestWith([InvalidTagValueException::class])]
    #[TestWith([RawTagModificationException::class])]
    #[TestWith([UnknownTagTypeException::class])]
    #[TestWith([CompressionException::class])]
    #[TestWith([CircularStructureException::class])]
    #[TestWith([StringDataFormatException::class])]
    #[TestWith([DeprecatedStringDataFormatException::class])]
    #[TestWith([BinaryDataException::class])]
    public function testExceptionExtendsNbtException(string $class): void
    {
        $this->assertTrue(is_subclass_of($class, NbtException::class), $class . " does not extend NbtException");
    }

    public function testDeprecatedStringDataFormatExceptionExtendsNewClass(): void
    {
        $this->assertTrue(is_subclass_of(DeprecatedStringDataFormatException::class, StringDataFormatException::class));
    }

    public function testJavaEncodingThrowsDeprecatedClassForCompatibility(): void
    {
        $this->expectException(DeprecatedStringDataFormatException::class);
        JavaEncoding::getInstance()->decode("\0");
    }

    public function testInvalidArrayValueThrowsInvalidTagValueException(): void
    {
        $this->expectException(InvalidTagValueException::class);
        $array = new IntArrayTag();
        $array[] = "not an int";
    }

    public function testUnnamedCompoundChildThrowsInvalidTagValueException(): void
    {
        $this->expectException(InvalidTagValueException::class);
        $compound = new CompoundTag();
        $compound[] = new IntTag();
    }

    public function testTooLongStringThrowsInvalidTagValueException(): void
    {
        $root = new CompoundTag()->set("string", new StringTag()->setValue(str_repeat("a", 0x10000)));

        $this->expectException(InvalidTagValueException::class);
        $root->write(new StringWriter());
    }

    public function testModifyingRawCompoundThrowsRawTagModificationException(): void
    {
        $writer = new StringWriter();
        new CompoundTag()->set("raw", new CompoundTag()->set("a", new IntTag()))->write($writer);
        $options = new TagOptions()->setRawCompoundPaths(["/raw"]);
        $root = Tag::load(new StringReader($writer->getStringData(), NbtFormat::JAVA_EDITION), $options);

        $this->expectException(RawTagModificationException::class);
        $root->getCompound("raw")->set("b", new IntTag());
    }

    public function testModifyingRawListThrowsRawTagModificationException(): void
    {
        $list = new ListTag()->setContentType(TagType::INT);
        $list[] = new IntTag();
        $list[] = new IntTag();
        $writer = new StringWriter();
        new CompoundTag()->set("list", $list)->write($writer);
        $options = new TagOptions()->setMaxListTagLength(1);
        $root = Tag::load(new StringReader($writer->getStringData(), NbtFormat::JAVA_EDITION), $options);

        $this->expectException(RawTagModificationException::class);
        $rawList = $root->getList("list");
        $rawList[] = new IntTag();
    }

    public function testUnknownTagTypeThrowsUnknownTagTypeException(): void
    {
        $this->expectException(UnknownTagTypeException::class);
        Tag::load(new StringReader("\x0d", NbtFormat::JAVA_EDITION));
    }

    public function testInvalidGZipDataThrowsCompressionException(): void
    {
        $this->expectException(CompressionException::class);
        new GZipCompressedStringReader("not gzip", NbtFormat::JAVA_EDITION);
    }

    public function testInvalidZLibDataThrowsCompressionException(): void
    {
        $this->expectException(CompressionException::class);
        new ZLibCompressedStringReader("not zlib", NbtFormat::JAVA_EDITION);
    }

    public function testCircularStructureThrowsCircularStructureException(): void
    {
        $outer = new CompoundTag();
        $inner = new CompoundTag();
        $outer->set("inner", $inner);
        $inner->set("outer", $outer);

        $this->expectException(CircularStructureException::class);
        $outer->write(new StringWriter());
    }

    public function testTruncatedLongThrowsBinaryDataException(): void
    {
        $this->expectException(BinaryDataException::class);
        new StringReader("\x01\x02", NbtFormat::JAVA_EDITION)->getDeserializer()->readLong();
    }
}
