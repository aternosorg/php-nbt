<?php

namespace Aternos\Nbt\IO\Writer;

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
     * NbtFormat::JAVA_EDITION, NbtFormat::BEDROCK_EDITION, or NbtFormat::BEDROCK_EDITION_NETWORK
     *
     * @return int
     */
    public function getFormat(): int;

    /**
     * @return NbtSerializer
     */
    public function getSerializer(): NbtSerializer;
}
