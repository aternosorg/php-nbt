<?php

declare(strict_types=1);

namespace Aternos\Nbt\Deserializer;

final readonly class DeserializerIntReadResult extends DeserializerReadResult
{
    public function __construct(public int $value, string $rawData)
    {
        parent::__construct($rawData);
    }
}
