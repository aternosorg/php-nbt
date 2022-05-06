<?php

namespace Aternos\Nbt\Deserializer;

class DeserializerFloatReadResult extends DeserializerReadResult
{
    public function __construct(protected float $value, string $rawData)
    {
        parent::__construct($rawData);
    }

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }
}
