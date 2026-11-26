<?php

namespace Aternos\Nbt\Tests;

use Aternos\Nbt\Tag\StringTag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SnbtTest extends TestCase
{
    public function testEveryTagType(): void
    {
        $this->assertSame(
            '{"byte": -128b, "short": -32768s, "int": -2147483648, ' .
            '"long": -9223372036854775808L, "longMax": 9223372036854775807L, ' .
            '"float": -1.5f, "double": 0.1d, ' .
            "\"string\": \"Hello, Wörld! \u{1F600} with NUL \0 byte\", \"emptyString\": \"\", " .
            '"byteArray": [B;0b, 1b, -1b, 127b, -128b], ' .
            '"intArray": [I;0, 1, -1, 2147483647, -2147483648], ' .
            '"longArray": [L;0L, 1L, -1L, 9223372036854775807L, -9223372036854775808L], ' .
            '"stringList": ["first", "second"], "emptyList": [], ' .
            '"compoundList": [{"id": 1}, {"id": 2}], "nestedList": [[1, 2], [3]], ' .
            '"emptyCompound": {}, "nested": {"inner": {"value": "deep"}}}',
            TagFixtures::everyTagType()->toSNBT()
        );
    }

    public static function stringProvider(): iterable
    {
        yield "plain" => ["abc", '"abc"'];
        yield "double quotes switch to single quotes" => ['say "hi"', "'say \"hi\"'"];
        yield "both quotes escape double quotes" => ['it\'s "hi"', '"it\'s \"hi\""'];
        yield "backslash is escaped" => ['a\b', '"a\\\\b"'];
    }

    #[DataProvider("stringProvider")]
    public function testStringQuoting(string $value, string $snbt): void
    {
        $this->assertSame($snbt, new StringTag()->setValue($value)->toSNBT());
    }
}
