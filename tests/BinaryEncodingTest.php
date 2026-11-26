<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tests;

use Aternos\Nbt\IO\Reader\StringReader;
use Aternos\Nbt\IO\Writer\StringWriter;
use Aternos\Nbt\NbtFormat;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * Pins the exact bytes each format uses for numbers and length prefixes
 */
class BinaryEncodingTest extends TestCase
{
    /**
     * Each case is: format, value type (method suffix of the serializer and deserializer), value, expected bytes as hex
     */
    public static function encodingProvider(): iterable
    {
        $java = NbtFormat::JAVA_EDITION;
        yield "java byte -128" => [$java, "Byte", -128, "80"];
        yield "java byte -1" => [$java, "Byte", -1, "ff"];
        yield "java byte 127" => [$java, "Byte", 127, "7f"];
        yield "java short -2" => [$java, "Short", -2, "fffe"];
        yield "java short max" => [$java, "Short", 32767, "7fff"];
        yield "java short min" => [$java, "Short", -32768, "8000"];
        yield "java int 1" => [$java, "Int", 1, "00000001"];
        yield "java int -2" => [$java, "Int", -2, "fffffffe"];
        yield "java int max" => [$java, "Int", 2147483647, "7fffffff"];
        yield "java int min" => [$java, "Int", -2147483648, "80000000"];
        yield "java long -2" => [$java, "Long", -2, "fffffffffffffffe"];
        yield "java float -1.5" => [$java, "Float", -1.5, "bfc00000"];
        yield "java float infinity" => [$java, "Float", INF, "7f800000"];
        yield "java float negative zero" => [$java, "Float", -0.0, "80000000"];
        yield "java double 0.1" => [$java, "Double", 0.1, "3fb999999999999a"];
        yield "java double negative infinity" => [$java, "Double", -INF, "fff0000000000000"];
        yield "java double negative zero" => [$java, "Double", -0.0, "8000000000000000"];
        yield "java length prefix 300" => [$java, "LengthPrefix", 300, "0000012c"];
        yield "java string length prefix is unsigned" => [$java, "StringLengthPrefix", 65535, "ffff"];

        $bedrock = NbtFormat::BEDROCK_EDITION;
        yield "bedrock byte -128" => [$bedrock, "Byte", -128, "80"];
        yield "bedrock short -2" => [$bedrock, "Short", -2, "feff"];
        yield "bedrock short max" => [$bedrock, "Short", 32767, "ff7f"];
        yield "bedrock short min" => [$bedrock, "Short", -32768, "0080"];
        yield "bedrock int 1" => [$bedrock, "Int", 1, "01000000"];
        yield "bedrock int -2" => [$bedrock, "Int", -2, "feffffff"];
        yield "bedrock int max" => [$bedrock, "Int", 2147483647, "ffffff7f"];
        yield "bedrock int min" => [$bedrock, "Int", -2147483648, "00000080"];
        yield "bedrock long -2" => [$bedrock, "Long", -2, "feffffffffffffff"];
        yield "bedrock float -1.5" => [$bedrock, "Float", -1.5, "0000c0bf"];
        yield "bedrock float infinity" => [$bedrock, "Float", INF, "0000807f"];
        yield "bedrock float negative zero" => [$bedrock, "Float", -0.0, "00000080"];
        yield "bedrock double 0.1" => [$bedrock, "Double", 0.1, "9a9999999999b93f"];
        yield "bedrock double negative infinity" => [$bedrock, "Double", -INF, "000000000000f0ff"];
        yield "bedrock length prefix 300" => [$bedrock, "LengthPrefix", 300, "2c010000"];
        yield "bedrock string length prefix is unsigned" => [$bedrock, "StringLengthPrefix", 65535, "ffff"];

        $network = NbtFormat::BEDROCK_EDITION_NETWORK;
        yield "network byte -1" => [$network, "Byte", -1, "ff"];
        yield "network short -2" => [$network, "Short", -2, "feff"];
        yield "network float -1.5" => [$network, "Float", -1.5, "0000c0bf"];
        yield "network double 0.1" => [$network, "Double", 0.1, "9a9999999999b93f"];
        yield "network int 0" => [$network, "Int", 0, "00"];
        yield "network int -1" => [$network, "Int", -1, "01"];
        yield "network int 1" => [$network, "Int", 1, "02"];
        yield "network int 300" => [$network, "Int", 300, "d804"];
        yield "network int max" => [$network, "Int", 2147483647, "feffffff0f"];
        yield "network int min" => [$network, "Int", -2147483648, "ffffffff0f"];
        yield "network long -1" => [$network, "Long", -1, "01"];
        yield "network long 300" => [$network, "Long", 300, "d804"];
        yield "network long max" => [$network, "Long", PHP_INT_MAX, "feffffffffffffffff01"];
        yield "network long min" => [$network, "Long", PHP_INT_MIN, "ffffffffffffffffff01"];
        yield "network length prefix 300" => [$network, "LengthPrefix", 300, "d804"];
        yield "network string length prefix 127" => [$network, "StringLengthPrefix", 127, "7f"];
        yield "network string length prefix 128" => [$network, "StringLengthPrefix", 128, "8001"];
        yield "network string length prefix 200" => [$network, "StringLengthPrefix", 200, "c801"];
        yield "network string length prefix is unsigned" => [$network, "StringLengthPrefix", 4294967295, "ffffffff0f"];
    }

    #[DataProvider("encodingProvider")]
    public function testValueIsWrittenAsExpectedBytes(int $format, string $type, int|float $value, string $expectedHex): void
    {
        $serializer = new StringWriter()->setFormat($format)->getSerializer();
        $serializer->{"write" . $type}($value);

        $this->assertSame($expectedHex, bin2hex($serializer->getWriter()->getStringData()));
    }

    /**
     * @throws Exception
     */
    #[DataProvider("encodingProvider")]
    public function testExpectedBytesAreReadAsValue(int $format, string $type, int|float $value, string $expectedHex): void
    {
        $bytes = hex2bin($expectedHex);
        $reader = new StringReader($bytes . "trailing", $format);
        $result = $reader->getDeserializer()->{"read" . $type}();

        $this->assertSame($value, $result->getValue());
        $this->assertSame($bytes, $result->getRawData());
        $this->assertSame(strlen($bytes), $reader->tell(), "Only the bytes of the value must be consumed");
    }

    #[TestWith([NbtFormat::JAVA_EDITION, "7fc00000"])]
    #[TestWith([NbtFormat::BEDROCK_EDITION, "0000c07f"])]
    public function testFloatNanIsReadAsNan(int $format, string $hex): void
    {
        $value = new StringReader(hex2bin($hex), $format)->getDeserializer()->readFloat()->getValue();
        $this->assertNan($value);
    }

    #[TestWith([NbtFormat::JAVA_EDITION])]
    #[TestWith([NbtFormat::BEDROCK_EDITION])]
    public function testDoubleNanSurvivesRoundTrip(int $format): void
    {
        $writer = new StringWriter()->setFormat($format);
        $writer->getSerializer()->writeDouble(NAN);
        $value = new StringReader($writer->getStringData(), $format)->getDeserializer()->readDouble()->getValue();
        $this->assertNan($value);
    }

    public static function truncatedDataProvider(): iterable
    {
        $java = NbtFormat::JAVA_EDITION;
        $bedrock = NbtFormat::BEDROCK_EDITION;
        $network = NbtFormat::BEDROCK_EDITION_NETWORK;

        yield "java byte without data" => [$java, "Byte", ""];
        yield "java short with 1 of 2 bytes" => [$java, "Short", "00"];
        yield "java int with 2 of 4 bytes" => [$java, "Int", "0001"];
        yield "java float with 3 of 4 bytes" => [$java, "Float", "000000"];
        yield "java double with 3 of 8 bytes" => [$java, "Double", "000000"];
        yield "java length prefix with 2 of 4 bytes" => [$java, "LengthPrefix", "0001"];
        yield "java string length prefix with 1 of 2 bytes" => [$java, "StringLengthPrefix", "00"];
        yield "bedrock byte without data" => [$bedrock, "Byte", ""];
        yield "bedrock short with 1 of 2 bytes" => [$bedrock, "Short", "00"];
        yield "bedrock int with 2 of 4 bytes" => [$bedrock, "Int", "0001"];
        yield "bedrock float with 3 of 4 bytes" => [$bedrock, "Float", "000000"];
        yield "bedrock double with 3 of 8 bytes" => [$bedrock, "Double", "000000"];
        yield "bedrock string length prefix with 1 of 2 bytes" => [$bedrock, "StringLengthPrefix", "00"];
        yield "network int without data" => [$network, "Int", ""];
        yield "network int ending with continuation bit" => [$network, "Int", "80"];
        yield "network int longer than 5 bytes" => [$network, "Int", "ffffffffff01"];
        yield "network long ending with continuation bit" => [$network, "Long", "ffff"];
        yield "network long longer than 10 bytes" => [$network, "Long", "ffffffffffffffffffff01"];
        yield "network length prefix ending with continuation bit" => [$network, "LengthPrefix", "80"];
        yield "network string length prefix ending with continuation bit" => [$network, "StringLengthPrefix", "80"];
    }

    #[DataProvider("truncatedDataProvider")]
    public function testTruncatedDataIsRejected(int $format, string $type, string $hex): void
    {
        $deserializer = new StringReader(hex2bin($hex), $format)->getDeserializer();

        $this->expectException(Exception::class);
        $deserializer->{"read" . $type}();
    }
}
