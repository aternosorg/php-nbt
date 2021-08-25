<?php

namespace Aternos\Nbt\Serializer;

class SerializerReadResult
{
    protected int $value;
    protected string $rawData;

    public function __construct(int $value, string $rawData)
    {
        $this->value = $value;
        $this->rawData = $rawData;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getRawData(): string
    {
        return $this->rawData;
    }
}
