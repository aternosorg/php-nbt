<?php

namespace Aternos\Nbt\Serializer;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\NbtFormat;
use pocketmine\utils\Binary;

class BedrockEditionNetworkNbtSerializer extends BedrockEditionNbtSerializer
{
    /**
     * @inheritDoc
     */
    public function readLengthPrefix(Reader $reader): SerializerReadResult
    {
        $raw = $reader->read(5);
        $offset = 0;
        $value = Binary::readVarInt($raw,$offset);
        $reader->returnData(substr($raw, $offset));
        return new SerializerReadResult($value, substr($raw, 0, $offset));
    }

    /**
     * @inheritDoc
     */
    public function encodeLengthPrefix(int $value): string
    {
        return Binary::writeVarInt($value);
    }

    /**
     * @inheritDoc
     */
    public function readStringLengthPrefix(Reader $reader): SerializerReadResult
    {
        $raw = $reader->read(5);
        $offset = 0;
        $value = Binary::readUnsignedVarInt($raw,$offset);
        $reader->returnData(substr($raw, $offset));
        return new SerializerReadResult($value, substr($raw, 0, $offset));
    }

    /**
     * @inheritDoc
     */
    public function encodeStringLengthPrefix(int $value): string
    {
        return Binary::writeUnsignedVarInt($value);
    }

    /**
     * @inheritDoc
     */
    public function readInt(Reader $reader): SerializerReadResult
    {
        $raw = $reader->read(5);
        $offset = 0;
        $value = Binary::readVarInt($raw,$offset);
        $reader->returnData(substr($raw, $offset));
        return new SerializerReadResult($value, substr($raw, 0, $offset));
    }

    /**
     * @inheritDoc
     */
    public function encodeInt(int $value): string
    {
        return Binary::writeVarInt($value);
    }

    /**
     * @inheritDoc
     */
    public function readLong(Reader $reader): SerializerReadResult
    {
        $raw = $reader->read(10);
        $offset = 0;
        $value = Binary::readVarLong($raw,$offset);
        $reader->returnData(substr($raw, $offset));
        return new SerializerReadResult($value, substr($raw, 0, $offset));
    }

    /**
     * @inheritDoc
     */
    public function encodeLong(int $value): string
    {
        return Binary::writeVarLong($value);
    }

    /**
     * @inheritDoc
     */
    public function getFormat(): int
    {
        return NbtFormat::BEDROCK_EDITION_NETWORK;
    }
}