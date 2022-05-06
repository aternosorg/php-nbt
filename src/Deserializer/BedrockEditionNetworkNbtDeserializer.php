<?php

namespace Aternos\Nbt\Deserializer;

use Aternos\Nbt\NbtFormat;
use pocketmine\utils\Binary;

class BedrockEditionNetworkNbtDeserializer extends BedrockEditionNbtDeserializer
{
    /**
     * @inheritDoc
     */
    public function getFormat(): int
    {
        return NbtFormat::BEDROCK_EDITION_NETWORK;
    }

    /**
     * @inheritDoc
     */
    public function readLengthPrefix(): DeserializerIntReadResult
    {
        $reader = $this->getReader();
        $raw = $reader->read(5);
        $offset = 0;
        $value = Binary::readVarInt($raw, $offset);
        $reader->returnData(substr($raw, $offset));
        return new DeserializerIntReadResult($value, substr($raw, 0, $offset));
    }

    /**
     * @inheritDoc
     */
    public function readStringLengthPrefix(): DeserializerIntReadResult
    {
        $reader = $this->getReader();
        $raw = $reader->read(5);
        $offset = 0;
        $value = Binary::readUnsignedVarInt($raw, $offset);
        $reader->returnData(substr($raw, $offset));
        return new DeserializerIntReadResult($value, substr($raw, 0, $offset));
    }

    /**
     * @inheritDoc
     */
    public function readInt(): DeserializerIntReadResult
    {
        $reader = $this->getReader();
        $raw = $reader->read(5);
        $offset = 0;
        $value = Binary::readVarInt($raw, $offset);
        $reader->returnData(substr($raw, $offset));
        return new DeserializerIntReadResult($value, substr($raw, 0, $offset));
    }

    /**
     * @inheritDoc
     */
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
