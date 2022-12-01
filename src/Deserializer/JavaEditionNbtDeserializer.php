<?php

namespace Aternos\Nbt\Deserializer;

use Aternos\Nbt\MachineByteOrder;
use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\String\JavaEncoding;
use Aternos\Nbt\String\StringDataFormatException;
use pocketmine\utils\Binary;

class JavaEditionNbtDeserializer extends NbtDeserializer
{
    /**
     * @inheritDoc
     */
    public function getFormat(): int
    {
        return NbtFormat::JAVA_EDITION;
    }

    /**
     * @inheritDoc
     */
    public function readLengthPrefix(): DeserializerIntReadResult
    {
        $raw = $this->getReader()->read(4);
        return new DeserializerIntReadResult(Binary::signInt(Binary::readInt($raw)), $raw);
    }

    /**
     * @inheritDoc
     */
    public function readStringLengthPrefix(): DeserializerIntReadResult
    {
        $raw = $this->getReader()->read(2);
        return new DeserializerIntReadResult(Binary::readShort($raw), $raw);
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
        return new DeserializerIntReadResult(Binary::readSignedShort($raw), $raw);
    }

    /**
     * @inheritDoc
     */
    public function readInt(): DeserializerIntReadResult
    {
        $raw = $this->getReader()->read(4);
        return new DeserializerIntReadResult(Binary::signInt(Binary::readInt($raw)), $raw);
    }

    /**
     * @inheritDoc
     */
    public function readLong(): DeserializerIntReadResult
    {
        $raw = $this->getReader()->read(8);
        $value = @unpack("q", MachineByteOrder::isLittleEndian() ? strrev($raw) : $raw)[1] ?? 0;
        return new DeserializerIntReadResult($value, $raw);
    }

    /**
     * @inheritDoc
     */
    public function readFloat(): DeserializerFloatReadResult
    {
        $raw = $this->getReader()->read(4);
        return new DeserializerFloatReadResult(Binary::readFloat($raw), $raw);
    }

    /**
     * @inheritDoc
     */
    public function readDouble(): DeserializerFloatReadResult
    {
        $raw = $this->getReader()->read(8);
        return new DeserializerFloatReadResult(Binary::readDouble($raw), $raw);
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
        return new DeserializerStringReadResult(JavaEncoding::getInstance()->decode($val), $length->getRawData() . $val);
    }
}
