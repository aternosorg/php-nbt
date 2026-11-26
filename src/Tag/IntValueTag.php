<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\Deserializer\DeserializerIntReadResult;
use Aternos\Nbt\Deserializer\NbtDeserializer;
use Aternos\Nbt\Exception\InvalidTagValueException;
use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Aternos\Nbt\Serializer\NbtSerializer;
use Override;

abstract class IntValueTag extends Tag
{
    public const int MIN_VALUE = PHP_INT_MIN;
    public const int MAX_VALUE = PHP_INT_MAX;

    protected int $value = 0;

    abstract protected static function readNumber(NbtDeserializer $deserializer): DeserializerIntReadResult;

    abstract protected static function writeNumber(NbtSerializer $serializer, int $value): void;

    public static function isValidValue(int $value): bool
    {
        return $value >= static::MIN_VALUE && $value <= static::MAX_VALUE;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @throws InvalidTagValueException if the value is outside MIN_VALUE and MAX_VALUE
     */
    public function setValue(int $value): static
    {
        if (!static::isValidValue($value)) {
            throw new InvalidTagValueException("Value " . $value . " is out of range for " . $this->getType()->getName() .
                " (" . static::MIN_VALUE . " to " . static::MAX_VALUE . ")");
        }
        $this->value = $value;
        return $this;
    }

    #[Override]
    protected function writeContent(Writer $writer): void
    {
        static::writeNumber($writer->getSerializer(), $this->value);
    }

    #[Override]
    protected function readContent(Reader $reader): void
    {
        $this->value = static::readNumber($reader->getDeserializer())->value;
    }

    #[Override]
    protected static function readContentRaw(Reader $reader, TagOptions $options): string
    {
        return static::readNumber($reader->getDeserializer())->rawData;
    }

    #[Override]
    protected function getValueString(): string
    {
        return (string)$this->value;
    }

    #[Override]
    public function jsonSerialize(): int
    {
        return $this->value;
    }

    #[Override]
    public function equals(Tag $tag): bool
    {
        return $tag instanceof IntValueTag && $this->getType() === $tag->getType() && $tag->value === $this->value;
    }
}
