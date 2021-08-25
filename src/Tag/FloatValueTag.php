<?php

namespace Aternos\Nbt\Tag;

abstract class FloatValueTag extends Tag
{
    protected float $value;

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
    public function setValue(float $value): FloatValueTag
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
}