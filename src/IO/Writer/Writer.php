<?php

declare(strict_types=1);

namespace Aternos\Nbt\IO\Writer;

use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\Serializer\NbtSerializer;

interface Writer
{
    /**
     * Write data
     *
     * @param string $data
     */
    public function write(string $data): void;

    /**
     * Get cursor position
     *
     * @return int
     */
    public function tell(): int;

    /**
     * NBT file format
     */
    public function getFormat(): NbtFormat;

    /**
     * @return NbtSerializer
     */
    public function getSerializer(): NbtSerializer;
}
