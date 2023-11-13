<?php

namespace Aternos\Nbt\Tag;

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
