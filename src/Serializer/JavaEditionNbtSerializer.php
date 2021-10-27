<?php

namespace Aternos\Nbt\Serializer;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\MachineByteOrder;
use Aternos\Nbt\NbtFormat;
use pocketmine\utils\Binary;

class JavaEditionNbtSerializer implements NbtSerializer
{
    /**
     * @inheritDoc
     */
    public function readLengthPrefix(Reader $reader): SerializerReadResult
    {
        $raw = $reader->read(4);
        return new SerializerReadResult(Binary::signInt(Binary::readInt($raw)), $raw);
    }

    /**
     * @inheritDoc
     */
    public function encodeLengthPrefix(int $value): string
    {
        return Binary::writeInt(Binary::unsignInt($value));
    }

    /**
     * @inheritDoc
     */
    public function readStringLengthPrefix(Reader $reader): SerializerReadResult
    {
        $raw = $reader->read(2);
        return new SerializerReadResult(Binary::readShort($raw), $raw);
    }

    /**
     * @inheritDoc
     */
    public function encodeStringLengthPrefix(int $value): string
    {
        return Binary::writeShort($value);
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
        return Binary::readSignedShort($data);
    }

    /**
     * @inheritDoc
     */
    public function encodeShort(int $value): string
    {
        return Binary::writeShort(Binary::unsignShort($value));
    }

    /**
     * @param string $data
     * @return int
     */
    public function decodeInt(string $data): int
    {
        return Binary::signInt(Binary::readInt($data));
    }

    /**
     * @inheritDoc
     */
    public function encodeInt(int $value): string
    {
        return Binary::writeInt(Binary::unsignInt($value));
    }

    /**
     * @param string $data
     * @return int
     */
    public function decodeLong(string $data): int
    {
        return @unpack("q", MachineByteOrder::isLittleEndian() ? strrev($data) : $data)[1] ?? 0;
    }

    /**
     * @inheritDoc
     */
    public function encodeLong(int $value): string
    {
        $packed = pack("q", $value);
        return MachineByteOrder::isLittleEndian() ? strrev($packed) : $packed;
    }

    /**
     * @inheritDoc
     */
    public function decodeFloat(string $data): float
    {
        return Binary::readFloat($data);
    }

    /**
     * @inheritDoc
     */
    public function encodeFloat(float $value): string
    {
        return Binary::writeFloat($value);
    }

    /**
     * @inheritDoc
     */
    public function decodeDouble(string $data): float
    {
        return Binary::readDouble($data);
    }

    /**
     * @inheritDoc
     */
    public function encodeDouble(float $value): string
    {
        return Binary::writeDouble($value);
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
        return NbtFormat::JAVA_EDITION;
    }
}