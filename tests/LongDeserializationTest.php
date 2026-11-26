<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tests;

use Aternos\Nbt\IO\Reader\StringReader;
use Aternos\Nbt\NbtFormat;
use Exception;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class LongDeserializationTest extends TestCase
{
    #[TestWith([NbtFormat::JAVA_EDITION, "\x01\x02\x03\x04\x05\x06\x07\x08", 0x0102030405060708], "java")]
    #[TestWith([NbtFormat::JAVA_EDITION, "\xff\xff\xff\xff\xff\xff\xff\xfe", -2], "java negative")]
    #[TestWith([NbtFormat::BEDROCK_EDITION, "\x08\x07\x06\x05\x04\x03\x02\x01", 0x0102030405060708], "bedrock")]
    #[TestWith([NbtFormat::BEDROCK_EDITION, "\xfe\xff\xff\xff\xff\xff\xff\xff", -2], "bedrock negative")]
    public function testReadsLong(int $format, string $data, int $expected): void
    {
        $result = new StringReader($data, $format)->getDeserializer()->readLong();

        $this->assertSame($expected, $result->getValue());
        $this->assertSame($data, $result->getRawData());
    }

    /**
     * @throws Exception
     */
    #[TestWith([NbtFormat::JAVA_EDITION, ""], "java empty")]
    #[TestWith([NbtFormat::JAVA_EDITION, "\x01\x02"], "java two bytes")]
    #[TestWith([NbtFormat::JAVA_EDITION, "\x01\x02\x03\x04\x05\x06\x07"], "java seven bytes")]
    #[TestWith([NbtFormat::BEDROCK_EDITION, ""], "bedrock empty")]
    #[TestWith([NbtFormat::BEDROCK_EDITION, "\x01\x02"], "bedrock two bytes")]
    #[TestWith([NbtFormat::BEDROCK_EDITION, "\x01\x02\x03\x04\x05\x06\x07"], "bedrock seven bytes")]
    public function testTruncatedLongThrows(int $format, string $data): void
    {
        $deserializer = new StringReader($data, $format)->getDeserializer();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Failed to read long: expected 8 bytes, got " . strlen($data));
        $deserializer->readLong();
    }
}
