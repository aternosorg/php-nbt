<?php

namespace Aternos\Nbt\Tag;

abstract class FloatValueTag extends Tag
{
    protected float $value = 0;

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * @param float $value
     * @return $this
     */
    public function setValue(float $value): static
    {
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
    public function jsonSerialize(): int|float
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function equals(Tag $tag): bool
    {
        return $tag instanceof FloatValueTag && $this->getType() === $tag->getType() &&
            $tag->getValue() === $this->getValue();
    }
}
