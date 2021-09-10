<?php

namespace Aternos\Nbt\Serializer;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\MachineByteOrder;
use Aternos\Nbt\NbtFormat;
use pocketmine\utils\Binary;

class BedrockEditionNbtSerializer implements NbtSerializer
{
    /**
     * @inheritDoc
     */
    public function readLengthPrefix(Reader $reader): SerializerReadResult
    {
        $raw = $reader->read(4);
        return new SerializerReadResult(Binary::signInt(Binary::readLInt($raw)), $raw);
    }

    /**
     * @inheritDoc
     */
    public function encodeLengthPrefix(int $value): string
    {
        return Binary::writeLInt(Binary::unsignInt($value));
    }

    /**
     * @inheritDoc
     */
    public function readStringLengthPrefix(Reader $reader): SerializerReadResult
    {
        $raw = $reader->read(2);
        return new SerializerReadResult(Binary::readLShort($raw), $raw);
    }

    /**
     * @inheritDoc
     */
    public function encodeStringLengthPrefix(int $value): string
    {
        return Binary::writeLShort($value);
    }

    /**
     * @inheritDoc
     */
    public function decodeByte(string $data): int
    {
        return Binary::readSignedByte($data);
    }

    /**
     * @inheritDoc
     */
    public function encodeByte(int $value): string
    {
        return Binary::writeByte(Binary::unsignByte($value));
    }

    /**
     * @inheritDoc
     */
    public function decodeShort(string $data): int
    {
        return Binary::readSignedLShort($data);
    }

    /**
     * @inheritDoc
     */
    public function encodeShort(int $value): string
    {
        return Binary::writeLShort(Binary::unsignShort($value));
    }

    /**
     * Decode an int
     *
     * @param string $data
     * @return int
     */
    protected function decodeInt(string $data): int
    {
        return Binary::signInt(Binary::readLInt($data));
    }

    /**
     * @inheritDoc
     */
    public function encodeInt(int $value): string
    {
        return Binary::writeLInt(Binary::unsignInt($value));
    }

    /**
     * Decode a long value
     *
     * @param string $data
     * @return int
     */
    protected function decodeLong(string $data): int
    {
        return @unpack("q", MachineByteOrder::isBigEndian() ? strrev($data) : $data)[1] ?? 0;
    }

    /**
     * @inheritDoc
     */
    public function encodeLong(int $value): string
    {
        $packed = pack("q", $value);
        return MachineByteOrder::isBigEndian() ? strrev($packed) : $packed;
    }

    /**
     * @inheritDoc
     */
    public function decodeFloat(string $data): float
    {
        return Binary::readLFloat($data);
    }

    /**
     * @inheritDoc
     */
    public function encodeFloat(float $value): string
    {
        return Binary::writeLFloat($value);
    }

    /**
     * @inheritDoc
     */
    public function decodeDouble(string $data): float
    {
        return Binary::readLDouble($data);
    }

    /**
     * @inheritDoc
     */
    public function encodeDouble(float $value): string
    {
        return Binary::writeLDouble($value);
    }

    /**
     * @inheritDoc
     */
    public function readInt(Reader $reader): SerializerReadResult
    {
        $raw = $reader->read(4);
        return new SerializerReadResult($this->decodeInt($raw), $raw);
    }

    /**
     * @inheritDoc
     */
    public function readLong(Reader $reader): SerializerReadResult
    {
        $raw = $reader->read(8);
        return new SerializerReadResult($this->decodeLong($raw), $raw);
    }

    /**
     * @inheritDoc
     */
    public function getFormat(): int
    {
        return NbtFormat::BEDROCK_EDITION;
    }
}