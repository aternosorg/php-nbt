<?php

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;

abstract class IntValueTag extends Tag
{
    protected int $value;

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
    public function setValue(int $value): IntValueTag
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