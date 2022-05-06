<?php

namespace Aternos\Nbt\IO\Reader;

use Aternos\Nbt\Deserializer\NbtDeserializer;

interface Reader
{
    /**
     * Read data
     *
     * @param int $length
     * @return string
     */
    public function read(int $length): string;

    /**
     * Check if all input data has been consumed
     *
     * @return bool
     */
    public function eof(): bool;

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
     * Return unused data to the reader buffer
     *
     * @param string $data
     */
    public function returnData(string $data): void;

    /**
     * @return NbtDeserializer
     */
    public function getDeserializer(): NbtDeserializer;
}
