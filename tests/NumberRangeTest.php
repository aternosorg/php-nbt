<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tests;

use Aternos\Nbt\Exception\InvalidTagValueException;
use Aternos\Nbt\Tag\ArrayValueTag;
use Aternos\Nbt\Tag\ByteArrayTag;
use Aternos\Nbt\Tag\ByteTag;
use Aternos\Nbt\Tag\IntArrayTag;
use Aternos\Nbt\Tag\IntTag;
use Aternos\Nbt\Tag\IntValueTag;
use Aternos\Nbt\Tag\LongArrayTag;
use Aternos\Nbt\Tag\LongTag;
use Aternos\Nbt\Tag\ShortTag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class NumberRangeTest extends TestCase
{
    /**
     * Each case is: tag class, smallest valid value, largest valid value
     */
    public static function rangeProvider(): iterable
    {
        yield "byte" => [ByteTag::class, -128, 127];
        yield "short" => [ShortTag::class, -32768, 32767];
        yield "int" => [IntTag::class, -2147483648, 2147483647];
    }

    /**
     * Each case is: array tag class, smallest valid value, largest valid value
     */
    public static function arrayRangeProvider(): iterable
    {
        yield "byte array" => [ByteArrayTag::class, -128, 127];
        yield "int array" => [IntArrayTag::class, -2147483648, 2147483647];
    }

    /**
     * @param class-string<IntValueTag> $class
     */
    #[DataProvider("rangeProvider")]
    public function testBoundariesAreAccepted(string $class, int $min, int $max): void
    {
        $this->assertSame($min, $class::MIN_VALUE);
        $this->assertSame($max, $class::MAX_VALUE);
        $this->assertSame($min, new $class()->setValue($min)->getValue());
        $this->assertSame($max, new $class()->setValue($max)->getValue());
    }

    /**
     * @param class-string<IntValueTag> $class
     */
    #[DataProvider("rangeProvider")]
    public function testValueBelowMinimumIsRejected(string $class, int $min, int $max): void
    {
        $tag = new $class()->setValue(1);

        $this->expectExceptionObject(new InvalidTagValueException(
            "Value " . ($min - 1) . " is out of range for " . $tag->getType()->getName() . " (" . $min . " to " . $max . ")"
        ));
        try {
            $tag->setValue($min - 1);
        } finally {
            $this->assertSame(1, $tag->getValue());
        }
    }

    /**
     * @param class-string<IntValueTag> $class
     */
    #[DataProvider("rangeProvider")]
    public function testValueAboveMaximumIsRejected(string $class, int $min, int $max): void
    {
        $tag = new $class()->setValue(1);

        $this->expectExceptionObject(new InvalidTagValueException(
            "Value " . ($max + 1) . " is out of range for " . $tag->getType()->getName() . " (" . $min . " to " . $max . ")"
        ));
        try {
            $tag->setValue($max + 1);
        } finally {
            $this->assertSame(1, $tag->getValue());
        }
    }

    #[TestWith([PHP_INT_MIN])]
    #[TestWith([PHP_INT_MAX])]
    public function testLongTagAcceptsEveryInt(int $value): void
    {
        $this->assertSame($value, new LongTag()->setValue($value)->getValue());
    }

    /**
     * @param class-string<ArrayValueTag> $class
     */
    #[DataProvider("arrayRangeProvider")]
    public function testArrayBoundariesAreAccepted(string $class, int $min, int $max): void
    {
        $array = new $class();
        $array[] = $min;
        $array[] = $max;

        $this->assertSame([$min, $max], $array->jsonSerialize());
    }

    /**
     * @param class-string<ArrayValueTag> $class
     */
    #[DataProvider("arrayRangeProvider")]
    public function testArrayValueBelowMinimumIsRejected(string $class, int $min, int $max): void
    {
        $array = new $class();

        $this->expectExceptionObject(new InvalidTagValueException(
            "Value " . ($min - 1) . " is out of range for " . $array->getType()->getName() . " (" . $min . " to " . $max . ")"
        ));
        $array[] = $min - 1;
    }

    /**
     * @param class-string<ArrayValueTag> $class
     */
    #[DataProvider("arrayRangeProvider")]
    public function testArrayValueAboveMaximumIsRejected(string $class, int $min, int $max): void
    {
        $array = new $class();
        $array[] = 1;

        $this->expectException(InvalidTagValueException::class);
        try {
            $array[0] = $max + 1;
        } finally {
            $this->assertSame([1], $array->jsonSerialize());
        }
    }

    public function testLongArrayAcceptsEveryInt(): void
    {
        $array = new LongArrayTag();
        $array[] = PHP_INT_MIN;
        $array[] = PHP_INT_MAX;

        $this->assertSame([PHP_INT_MIN, PHP_INT_MAX], $array->jsonSerialize());
    }

    public function testArrayStillRejectsValuesThatAreNotInts(): void
    {
        $array = new ByteArrayTag();

        $this->expectExceptionObject(new InvalidTagValueException("Invalid value of type string for TAG_Byte_Array"));
        $array[] = "1";
    }
}
