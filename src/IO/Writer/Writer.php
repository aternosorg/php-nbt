<?php

declare(strict_types=1);

namespace Aternos\Nbt\IO\Writer;

use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\Serializer\NbtSerializer;

interface Writer
{
    public function write(string $data): void;

    /**
     * Get cursor position
     */
    public function tell(): int;

    /**
     * NBT format flavor of the written data
     */
    public function getFormat(): NbtFormat;

    public function getSerializer(): NbtSerializer;
}
