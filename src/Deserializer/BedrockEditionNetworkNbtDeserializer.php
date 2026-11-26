<?php

declare(strict_types=1);

namespace Aternos\Nbt\Deserializer;

use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\IO\Binary;
use Override;

class BedrockEditionNetworkNbtDeserializer extends BedrockEditionNbtDeserializer
{
    #[Override]
    public function getFormat(): int
    {
        return NbtFormat::BEDROCK_EDITION_NETWORK;
    }

    #[Override]
    public function readLengthPrefix(): DeserializerIntReadResult
    {
        $reader = $this->getReader();
        $raw = $reader->read(5);
        $offset = 0;
        $value = Binary::readVarInt($raw, $offset);
        $reader->returnData(substr($raw, $offset));
        return new DeserializerIntReadResult($value, substr($raw, 0, $offset));
    }

    #[Override]
    public function readStringLengthPrefix(): DeserializerIntReadResult
    {
        $reader = $this->getReader();
        $raw = $reader->read(5);
        $offset = 0;
        $value = Binary::readUnsignedVarInt($raw, $offset);
        $reader->returnData(substr($raw, $offset));
        return new DeserializerIntReadResult($value, substr($raw, 0, $offset));
    }

    #[Override]
    public function readInt(): DeserializerIntReadResult
    {
        $reader = $this->getReader();
        $raw = $reader->read(5);
        $offset = 0;
        $value = Binary::readVarInt($raw, $offset);
        $reader->returnData(substr($raw, $offset));
        return new DeserializerIntReadResult($value, substr($raw, 0, $offset));
    }

    #[Override]
    public function readLong(): DeserializerIntReadResult
    {
        $reader = $this->getReader();
        $raw = $reader->read(10);
        $offset = 0;
        $value = Binary::readVarLong($raw, $offset);
        $reader->returnData(substr($raw, $offset));
        return new DeserializerIntReadResult($value, substr($raw, 0, $offset));
    }
}
