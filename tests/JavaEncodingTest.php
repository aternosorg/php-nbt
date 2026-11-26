<?php

namespace Aternos\Nbt\Tests;

use Aternos\Nbt\String\JavaEncoding;
use Aternos\Nbt\String\StringDataFormatException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class JavaEncodingTest extends TestCase
{
    public static function encodingProvider(): iterable
    {
        yield "ascii" => ["abc", "abc"];
        yield "NUL is encoded as two bytes" => ["\0", "\xC0\x80"];
        yield "two byte character" => ["ä", "\xC3\xA4"];
        yield "three byte character" => ["€", "\xE2\x82\xAC"];
        yield "supplementary character as surrogate pair" => ["\u{1F600}", "\xED\xA0\xBD\xED\xB8\x80"];
        yield "empty" => ["", ""];
    }

    #[DataProvider("encodingProvider")]
    public function testEncode(string $utf8, string $javaEncoded): void
    {
        $this->assertSame($javaEncoded, JavaEncoding::getInstance()->encode($utf8));
    }

    /**
     * @throws StringDataFormatException
     */
    #[DataProvider("encodingProvider")]
    public function testDecode(string $utf8, string $javaEncoded): void
    {
        $this->assertSame($utf8, JavaEncoding::getInstance()->decode($javaEncoded));
    }

    public static function invalidDataProvider(): iterable
    {
        yield "raw NUL byte" => ["a\0b"];
        yield "invalid continuation byte" => ["\xC3\x41"];
        yield "truncated two byte character" => ["\xC3"];
        yield "invalid lead byte" => ["\xFF"];
        yield "truncated three byte character" => ["\xE2\x82"];
    }

    #[DataProvider("invalidDataProvider")]
    public function testDecodeRejectsInvalidData(string $data): void
    {
        $this->expectException(StringDataFormatException::class);
        JavaEncoding::getInstance()->decode($data);
    }
}
