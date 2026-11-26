<?php

declare(strict_types=1);

namespace Aternos\Nbt\Deserializer;

final readonly class DeserializerStringReadResult extends DeserializerReadResult
{
    public function __construct(public string $value, string $rawData)
    {
        parent::__construct($rawData);
    }
}
