<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Override;

abstract class IntValueTag extends Tag
{
    protected int $value = 0;

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
     */
    public function setValue(int $value): static
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
    public function jsonSerialize(): int
    {
        return $this->value;
    }

    #[Override]
    public function equals(Tag $tag): bool
    {
        return $tag instanceof IntValueTag && $this->getType() === $tag->getType() &&
            $tag->getValue() === $this->getValue();
    }
}
