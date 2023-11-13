<?php

namespace Aternos\Nbt\Deserializer;

use Aternos\Nbt\MachineByteOrder;
use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\String\StringDataFormatException;
use pocketmine\utils\Binary;

class BedrockEditionNbtDeserializer extends NbtDeserializer
{
    /**
     * @inheritDoc
     */
    public function getFormat(): int
    {
        return NbtFormat::BEDROCK_EDITION;
    }

    /**
     * @inheritDoc
     */
    public function readLengthPrefix(): DeserializerIntReadResult
    {
        $raw = $this->getReader()->read(4);
        return new DeserializerIntReadResult(Binary::signInt(Binary::readLInt($raw)), $raw);
    }

    /**
     * @inheritDoc
     */
    public function readStringLengthPrefix(): DeserializerIntReadResult
    {
        $raw = $this->getReader()->read(2);
        return new DeserializerIntReadResult(Binary::readLShort($raw), $raw);
    }

    /**
     * @inheritDoc
     */
    public function readByte(): DeserializerIntReadResult
    {
        $raw = $this->getReader()->read(1);
        return new DeserializerIntReadResult(Binary::readSignedByte($raw), $raw);
    }

    /**
     * @inheritDoc
     */
    public function readShort(): DeserializerIntReadResult
    {
        $raw = $this->getReader()->read(2);
        return new DeserializerIntReadResult(Binary::readSignedLShort($raw), $raw);
    }

    /**
     * @inheritDoc
     */
    public function readInt(): DeserializerIntReadResult
    {
        $raw = $this->getReader()->read(4);
        return new DeserializerIntReadResult(Binary::signInt(Binary::readLInt($raw)), $raw);
    }

    /**
     * @inheritDoc
     */
    public function readLong(): DeserializerIntReadResult
    {
        $raw = $this->getReader()->read(8);
        $value = @unpack("q", MachineByteOrder::isBigEndian() ? strrev($raw) : $raw)[1] ?? 0;
        return new DeserializerIntReadResult($value, $raw);
    }

    /**
     * @inheritDoc
     */
    public function readFloat(): DeserializerFloatReadResult
    {
        $raw = $this->getReader()->read(4);
        return new DeserializerFloatReadResult(Binary::readLFloat($raw), $raw);
    }

    /**
     * @inheritDoc
     */
    public function readDouble(): DeserializerFloatReadResult
    {
        $raw = $this->getReader()->read(8);
        return new DeserializerFloatReadResult(Binary::readLDouble($raw), $raw);
    }

    /**
     * @inheritDoc
     * @throws StringDataFormatException
     */
    public function readString(): DeserializerStringReadResult
    {
        $length = $this->readStringLengthPrefix();
        $val = $this->getReader()->read($length->getValue());
        if(strlen($val) !== $length->getValue()){
            throw new StringDataFormatException("Failed to read string: expected length " . $length->getValue() . ", got " . strlen($val));
        }
        return new DeserializerStringReadResult($val, $length->getRawData() . $val);
    }
}
