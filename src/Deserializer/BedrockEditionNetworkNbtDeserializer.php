<?php

declare(strict_types=1);

namespace Aternos\Nbt\Deserializer;

use Aternos\Nbt\NbtFormat;
use Override;
use pocketmine\utils\Binary;

class BedrockEditionNetworkNbtDeserializer extends BedrockEditionNbtDeserializer
{
    /**
     * Maximum length of a 32-bit VarInt in bytes
     */
    protected const int MAX_VAR_INT_LENGTH = 5;

    /**
     * Maximum length of a 64-bit VarLong in bytes
     */
    protected const int MAX_VAR_LONG_LENGTH = 10;

    #[Override]
    public function getFormat(): NbtFormat
    {
        return NbtFormat::BedrockEditionNetwork;
    }

    #[Override]
    public function readLengthPrefix(): DeserializerIntReadResult
    {
        return $this->readInt();
    }

    #[Override]
    public function readStringLengthPrefix(): DeserializerIntReadResult
    {
        $raw = $this->reader->read(self::MAX_VAR_INT_LENGTH);
        $offset = 0;
        $value = Binary::readUnsignedVarInt($raw, $offset);
        return $this->createResultAndReturnUnusedData($value, $raw, $offset);
    }

    #[Override]
    public function readInt(): DeserializerIntReadResult
    {
        $raw = $this->reader->read(self::MAX_VAR_INT_LENGTH);
        $offset = 0;
        $value = Binary::readVarInt($raw, $offset);
        return $this->createResultAndReturnUnusedData($value, $raw, $offset);
    }

    #[Override]
    public function readLong(): DeserializerIntReadResult
    {
        $raw = $this->reader->read(self::MAX_VAR_LONG_LENGTH);
        $offset = 0;
        $value = Binary::readVarLong($raw, $offset);
        return $this->createResultAndReturnUnusedData($value, $raw, $offset);
    }

    /**
     * Variable length numbers are read with their maximum length, the bytes after $usedLength go back to the reader
     */
    protected function createResultAndReturnUnusedData(int $value, string $raw, int $usedLength): DeserializerIntReadResult
    {
        $this->reader->returnData(substr($raw, $usedLength));
        return new DeserializerIntReadResult($value, substr($raw, 0, $usedLength));
    }
}
