<?php

declare(strict_types=1);

namespace Aternos\Nbt\Deserializer;

final readonly class DeserializerIntReadResult extends DeserializerReadResult
{
    public int $value;

    public function __construct(int $value, string $rawData)
    {
        parent::__construct($rawData);
        $this->value = $value;
    }
}
