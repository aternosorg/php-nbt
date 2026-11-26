<?php

declare(strict_types=1);

namespace Aternos\Nbt\Deserializer;

final readonly class DeserializerStringReadResult extends DeserializerReadResult
{
    public string $value;

    public function __construct(string $value, string $rawData)
    {
        parent::__construct($rawData);
        $this->value = $value;
    }
}
