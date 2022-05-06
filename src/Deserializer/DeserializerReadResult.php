<?php

namespace Aternos\Nbt\Deserializer;

abstract class DeserializerReadResult
{
    public function __construct(protected string $rawData)
    {
    }

    /**
     * @return string
     */
    public function getRawData(): string
    {
        return $this->rawData;
    }
}
