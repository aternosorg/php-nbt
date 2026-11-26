<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Override;

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

    #[Override]
    protected function getValueString(): string
    {
        return strval($this->value);
    }

    #[Override]
    public function jsonSerialize(): int|float
    {
        return $this->value;
    }

    #[Override]
    public function equals(Tag $tag): bool
    {
        return $tag instanceof FloatValueTag && $this->getType() === $tag->getType() &&
            $tag->getValue() === $this->getValue();
    }
}
