<?php

declare(strict_types=1);

namespace Aternos\Nbt\IO\Reader;

use Aternos\Nbt\Deserializer\NbtDeserializer;
use Aternos\Nbt\NbtFormat;

interface Reader
{
    /**
     * Read up to $length bytes, fewer if the end of the data is reached
     */
    public function read(int $length): string;

    /**
     * Check if all input data has been consumed
     */
    public function eof(): bool;

    /**
     * Get cursor position
     */
    public function tell(): int;

    /**
     * NBT format flavor of the data
     */
    public function getFormat(): NbtFormat;

    /**
     * Return unused data to the reader buffer
     */
    public function returnData(string $data): void;

    public function getDeserializer(): NbtDeserializer;
}
