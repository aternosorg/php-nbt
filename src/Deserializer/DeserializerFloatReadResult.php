<?php

declare(strict_types=1);

namespace Aternos\Nbt\Deserializer;

final readonly class DeserializerFloatReadResult extends DeserializerReadResult
{
    public float $value;

    public function __construct(float $value, string $rawData)
    {
        parent::__construct($rawData);
        $this->value = $value;
    }
}
