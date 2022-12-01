<?php

namespace Aternos\Nbt\Deserializer;

class DeserializerStringReadResult extends DeserializerReadResult
{
    public function __construct(protected string $value, string $rawData)
    {
        parent::__construct($rawData);
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getRawLength(): int
    {
        return strlen($this->getRawData());
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return strlen($this->value);
    }
}
