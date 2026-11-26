<?php

declare(strict_types=1);

namespace Aternos\Nbt\Deserializer;

final readonly class DeserializerFloatReadResult extends DeserializerReadResult
{
    public function __construct(public float $value, string $rawData)
    {
        parent::__construct($rawData);
    }
}
