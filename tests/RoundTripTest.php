<?php

namespace Aternos\Nbt\Tests;

use Aternos\Nbt\IO\Reader\GZipCompressedStringReader;
use Aternos\Nbt\IO\Reader\StringReader;
use Aternos\Nbt\IO\Reader\ZLibCompressedStringReader;
use Aternos\Nbt\IO\Writer\GZipCompressedStringWriter;
use Aternos\Nbt\IO\Writer\StringWriter;
use Aternos\Nbt\IO\Writer\ZLibCompressedStringWriter;
use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\Tag\CompoundTag;
use Aternos\Nbt\Tag\IntTag;
use Aternos\Nbt\Tag\ListTag;
use Aternos\Nbt\Tag\StringTag;
use Aternos\Nbt\Tag\Tag;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class RoundTripTest extends TestCase
{
    public static function formatAndCompressionProvider(): iterable
    {
        $formats = [
            "java" => NbtFormat::JAVA_EDITION,
            "bedrock" => NbtFormat::BEDROCK_EDITION,
            "bedrock network" => NbtFormat::BEDROCK_EDITION_NETWORK,
        ];
        $compressions = [
            "uncompressed" => [StringWriter::class, StringReader::class],
            "gzip" => [GZipCompressedStringWriter::class, GZipCompressedStringReader::class],
            "zlib" => [ZLibCompressedStringWriter::class, ZLibCompressedStringReader::class],
        ];
        foreach ($formats as $formatName => $format) {
            foreach ($compressions as $compressionName => [$writerClass, $readerClass]) {
                yield $formatName . ", " . $compressionName => [$format, $writerClass, $readerClass];
            }
        }
    }

    /**
     * @param class-string<StringWriter> $writerClass
     * @param class-string<StringReader> $readerClass
     * @throws Exception
     */
    #[DataProvider("formatAndCompressionProvider")]
    public function testEveryTagTypeSurvivesRoundTrip(int $format, string $writerClass, string $readerClass): void
    {
        $original = TagFixtures::everyTagType();

        $writer = new $writerClass()->setFormat($format);
        $original->write($writer);
        $data = $writer->getStringData();

        $loaded = Tag::load(new $readerClass($data, $format));

        $this->assertInstanceOf(CompoundTag::class, $loaded);
        $this->assertTrue($original->equals($loaded), "Loaded tag differs from original:\n" . $loaded);
        $this->assertTrue($loaded->equals($original));
        $this->assertSame(
            $this->writeUncompressed($original, $format),
            $this->writeUncompressed($loaded, $format),
            "Rewriting the loaded tag must produce the same bytes"
        );
    }

    /**
     * @throws Exception
     */
    #[TestWith([NbtFormat::JAVA_EDITION], "java")]
    #[TestWith([NbtFormat::BEDROCK_EDITION], "bedrock")]
    #[TestWith([NbtFormat::BEDROCK_EDITION_NETWORK], "bedrock network")]
    public function testConvertingBetweenFormatsKeepsValues(int $format): void
    {
        $original = TagFixtures::everyTagType();
        foreach ([NbtFormat::JAVA_EDITION, NbtFormat::BEDROCK_EDITION, NbtFormat::BEDROCK_EDITION_NETWORK] as $targetFormat) {
            $loaded = $this->load($this->writeUncompressed($original, $format), $format);
            $converted = $this->load($this->writeUncompressed($loaded, $targetFormat), $targetFormat);
            $this->assertTrue($original->equals($converted));
        }
    }

    /**
     * @throws Exception
     */
    public function testJavaEditionEncoding(): void
    {
        $root = new CompoundTag()
            ->set("a", new IntTag()->setValue(1));

        $this->assertSame(
            "\x0a\x00\x00" .
            "\x03\x00\x01a\x00\x00\x00\x01" .
            "\x00",
            $this->writeUncompressed($root, NbtFormat::JAVA_EDITION)
        );
    }

    /**
     * @throws Exception
     */
    public function testBedrockEditionEncoding(): void
    {
        $root = new CompoundTag()
            ->set("a", new IntTag()->setValue(1));

        $this->assertSame(
            "\x0a\x00\x00" .
            "\x03\x01\x00a\x01\x00\x00\x00" .
            "\x00",
            $this->writeUncompressed($root, NbtFormat::BEDROCK_EDITION)
        );
    }

    /**
     * @throws Exception
     */
    public function testBedrockEditionNetworkEncoding(): void
    {
        $root = new CompoundTag()
            ->set("a", new IntTag()->setValue(-1));

        // VarInt string length prefix, zigzag VarInt for ints (-1 => 1)
        $this->assertSame(
            "\x0a\x00" .
            "\x03\x01a\x01" .
            "\x00",
            $this->writeUncompressed($root, NbtFormat::BEDROCK_EDITION_NETWORK)
        );
    }

    /**
     * @throws Exception
     */
    public function testBedrockEditionAllowsListAsRoot(): void
    {
        $list = TagFixtures::intList(1, 2, 3);
        $data = $this->writeUncompressed($list, NbtFormat::BEDROCK_EDITION);
        $loaded = $this->load($data, NbtFormat::BEDROCK_EDITION);

        $this->assertInstanceOf(ListTag::class, $loaded);
        $this->assertTrue($list->equals($loaded));
    }

    public function testJavaEditionRejectsListAsRoot(): void
    {
        $this->expectException(Exception::class);
        $this->writeUncompressed(TagFixtures::intList(1), NbtFormat::JAVA_EDITION);
    }

    public function testInvalidGZipDataIsRejected(): void
    {
        $this->expectException(Exception::class);
        new GZipCompressedStringReader("not gzip", NbtFormat::JAVA_EDITION);
    }

    public function testInvalidZLibDataIsRejected(): void
    {
        $this->expectException(Exception::class);
        new ZLibCompressedStringReader("not zlib", NbtFormat::JAVA_EDITION);
    }

    public function testUnknownTagTypeIsRejected(): void
    {
        $this->expectException(Exception::class);
        $this->load("\x0d\x00\x00", NbtFormat::JAVA_EDITION);
    }

    public function testTruncatedStringIsRejected(): void
    {
        $this->expectException(Exception::class);
        // compound named with a declared length of 5, but only 2 bytes follow
        $this->load("\x0a\x00\x05ab", NbtFormat::JAVA_EDITION);
    }

    public function testTruncatedBedrockStringIsRejected(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Failed to read string");
        // compound named with a declared little endian length of 5, but only 2 bytes follow
        $this->load("\x0a\x05\x00ab", NbtFormat::BEDROCK_EDITION);
    }

    /**
     * Root compound containing a list "l" whose content type 0x63 does not exist
     */
    protected const string UNKNOWN_LIST_CONTENT_TYPE = "\x0a\x00\x00" . "\x09\x00\x01l" . "\x63" . "\x00\x00\x00\x01" . "\x00";

    public function testUnknownListContentTypeIsRejected(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Unknown ListTag content type 99");
        $this->load(self::UNKNOWN_LIST_CONTENT_TYPE, NbtFormat::JAVA_EDITION);
    }

    public function testUnknownListContentTypeIsRejectedWhenReadingRaw(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Unknown ListTag content type 99");
        Tag::loadRaw(new StringReader(self::UNKNOWN_LIST_CONTENT_TYPE, NbtFormat::JAVA_EDITION));
    }

    public function testUnknownTagTypeIsRejectedWhenReadingRaw(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Unknown NBT tag type 13");
        Tag::loadRaw(new StringReader("\x0d\x00\x00", NbtFormat::JAVA_EDITION));
    }

    public function testTagWithoutNameCannotBeWrittenAsNamedTag(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("does not have a name value");
        new IntTag()->writeData(new StringWriter());
    }

    /**
     * @throws Exception
     */
    public function testStringAboveMaximumLengthCannotBeWritten(): void
    {
        $root = new CompoundTag()->set("string", new StringTag()->setValue(str_repeat("a", 0x10000)));
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("String exceeds maximum length");
        $root->write(new StringWriter());
    }

    #[TestWith([NbtFormat::JAVA_EDITION])]
    #[TestWith([NbtFormat::BEDROCK_EDITION])]
    #[TestWith([NbtFormat::BEDROCK_EDITION_NETWORK])]
    public function testSerializersAndDeserializersReportTheirFormat(int $format): void
    {
        $this->assertSame($format, new StringReader("", $format)->getDeserializer()->getFormat());
        $this->assertSame($format, new StringWriter()->setFormat($format)->getSerializer()->getFormat());
    }

    public function testChangingReaderFormatReplacesDeserializer(): void
    {
        $reader = new StringReader("", NbtFormat::JAVA_EDITION);
        $reader->getDeserializer();
        $reader->setFormat(NbtFormat::BEDROCK_EDITION);

        $this->assertSame(NbtFormat::BEDROCK_EDITION, $reader->getFormat());
        $this->assertSame(NbtFormat::BEDROCK_EDITION, $reader->getDeserializer()->getFormat());
    }

    /**
     * @throws Exception
     */
    public function testReaderAndWriterPositions(): void
    {
        $writer = new StringWriter();
        $this->assertSame(0, $writer->tell());
        $writer->getSerializer()->writeString("ab");
        $this->assertSame(4, $writer->tell());

        $reader = new StringReader($writer->getStringData(), NbtFormat::JAVA_EDITION);
        $this->assertFalse($reader->eof());
        $string = $reader->getDeserializer()->readString();
        $this->assertSame(4, $reader->tell());
        $this->assertTrue($reader->eof());
        $this->assertSame(2, $string->getLength());
        $this->assertSame(4, $string->getRawLength());
    }

    /**
     * @throws Exception
     */
    protected function writeUncompressed(Tag $tag, int $format): string
    {
        $writer = new StringWriter()->setFormat($format);
        $tag->write($writer);
        return $writer->getStringData();
    }

    /**
     * @throws Exception
     */
    protected function load(string $data, int $format): Tag
    {
        return Tag::load(new StringReader($data, $format));
    }
}
