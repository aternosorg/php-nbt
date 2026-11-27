<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\Exception\InvalidTagValueException;

abstract class IntValueTag extends Tag
{
    public const int MIN_VALUE = PHP_INT_MIN;
    public const int MAX_VALUE = PHP_INT_MAX;

    protected int $value = 0;

    /**
     * Whether $value fits into a tag of this type
     */
    public static function isValidValue(int $value): bool
    {
        return $value >= static::MIN_VALUE && $value <= static::MAX_VALUE;
    }

    /**
     * Throw if $value does not fit into a tag of this type
     *
     * @param TagType $holderType the type of the tag that holds the value, named in the error message
     * @throws InvalidTagValueException if $value is outside MIN_VALUE and MAX_VALUE
     */
    public static function checkValueRange(int $value, TagType $holderType): void
    {
        if (!static::isValidValue($value)) {
            throw new InvalidTagValueException("Value " . $value . " is out of range for " . $holderType->getName() .
                " (" . static::MIN_VALUE . " to " . static::MAX_VALUE . ")");
        }
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @param int $value
     * @return $this
     * @throws InvalidTagValueException if $value is outside MIN_VALUE and MAX_VALUE
     */
    public function setValue(int $value): static
    {
        static::checkValueRange($value, $this->getType());
        $this->value = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function getValueString(): string
    {
        return strval($this->value);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): int
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function equals(Tag $tag): bool
    {
        return $tag instanceof IntValueTag && $this->getType() === $tag->getType() &&
            $tag->getValue() === $this->getValue();
    }
}
