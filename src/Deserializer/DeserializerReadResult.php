<?php

declare(strict_types=1);

namespace Aternos\Nbt\Deserializer;

abstract readonly class DeserializerReadResult
{
    /**
     * @param string $rawData the bytes the value was read from
     */
    public function __construct(public string $rawData)
    {
    }
}
