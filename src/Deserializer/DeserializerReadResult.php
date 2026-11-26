<?php

declare(strict_types=1);

namespace Aternos\Nbt\Deserializer;

abstract readonly class DeserializerReadResult
{
    /**
     * The bytes the value was read from
     */
    public string $rawData;

    public function __construct(string $rawData)
    {
        $this->rawData = $rawData;
    }
}
