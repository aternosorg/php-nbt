<?php

declare(strict_types=1);

namespace Aternos\Nbt\Deserializer;

use Aternos\Nbt\MachineByteOrder;
use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\String\JavaEncoding;
use Aternos\Nbt\String\StringDataFormatException;
use Aternos\Nbt\IO\Binary;
use Override;

class JavaEditionNbtDeserializer extends NbtDeserializer
{
    #[Override]
    public function getFormat(): int
    {
        return NbtFormat::JAVA_EDITION;
    }

    #[Override]
    public function readLengthPrefix(): DeserializerIntReadResult
    {
        $raw = $this->getReader()->read(4);
        return new DeserializerIntReadResult(Binary::readInt($raw), $raw);
    }

    #[Override]
    public function readStringLengthPrefix(): DeserializerIntReadResult
    {
        $raw = $this->getReader()->read(2);
        return new DeserializerIntReadResult(Binary::readShort($raw), $raw);
    }

    #[Override]
    public function readByte(): DeserializerIntReadResult
    {
        $raw = $this->getReader()->read(1);
        return new DeserializerIntReadResult(Binary::readSignedByte($raw), $raw);
    }

    #[Override]
    public function readShort(): DeserializerIntReadResult
    {
        $raw = $this->getReader()->read(2);
        return new DeserializerIntReadResult(Binary::readSignedShort($raw), $raw);
    }

    #[Override]
    public function readInt(): DeserializerIntReadResult
    {
        $raw = $this->getReader()->read(4);
        return new DeserializerIntReadResult(Binary::readInt($raw), $raw);
    }

    #[Override]
    public function readLong(): DeserializerIntReadResult
    {
        $raw = $this->readRawLong();
        $value = unpack("q", MachineByteOrder::isLittleEndian() ? strrev($raw) : $raw)[1];
        return new DeserializerIntReadResult($value, $raw);
    }

    #[Override]
    public function readFloat(): DeserializerFloatReadResult
    {
        $raw = $this->getReader()->read(4);
        return new DeserializerFloatReadResult(Binary::readFloat($raw), $raw);
    }

    #[Override]
    public function readDouble(): DeserializerFloatReadResult
    {
        $raw = $this->getReader()->read(8);
        return new DeserializerFloatReadResult(Binary::readDouble($raw), $raw);
    }

    /**
     * @throws StringDataFormatException
     */
    #[Override]
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
