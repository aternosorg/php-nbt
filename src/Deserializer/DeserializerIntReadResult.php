<?php

namespace Aternos\Nbt\Deserializer;

class DeserializerIntReadResult extends DeserializerReadResult
{
    public function __construct(protected int $value, string $rawData)
    {
        parent::__construct($rawData);
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }
}
