<?php

declare(strict_types=1);

namespace Aternos\Nbt\Deserializer;

use Aternos\Nbt\Exception\StringDataFormatException;
use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\NbtFormat;

abstract class NbtDeserializer
{
    public function __construct(public readonly Reader $reader)
    {
    }

    abstract public function getFormat(): NbtFormat;

    /**
     * Read an NBT length prefix (TAG_List, TAG_Byte_Array, TAG_Int_Array, and TAG_Long_Array)
     */
    abstract public function readLengthPrefix(): DeserializerIntReadResult;

    /**
     * Read an NBT string length prefix
     */
    abstract public function readStringLengthPrefix(): DeserializerIntReadResult;

    abstract public function readByte(): DeserializerIntReadResult;

    abstract public function readShort(): DeserializerIntReadResult;

    abstract public function readInt(): DeserializerIntReadResult;

    abstract public function readLong(): DeserializerIntReadResult;

    abstract public function readFloat(): DeserializerFloatReadResult;

    abstract public function readDouble(): DeserializerFloatReadResult;

    /**
     * Convert string data from the format's encoding to UTF-8
     *
     * @throws StringDataFormatException
     */
    abstract protected function decodeString(string $data): string;

    /**
     * @throws StringDataFormatException
     */
    public function readString(): DeserializerStringReadResult
    {
        $length = $this->readStringLengthPrefix();
        $data = $this->readStringData($length->value);
        return new DeserializerStringReadResult($this->decodeString($data), $length->rawData . $data);
    }

    /**
     * Read the raw bytes of a string, including the length prefix, without decoding them
     *
     * @throws StringDataFormatException
     */
    public function readRawString(): string
    {
        $length = $this->readStringLengthPrefix();
        return $length->rawData . $this->readStringData($length->value);
    }

    /**
     * @throws StringDataFormatException
     */
    protected function readStringData(int $length): string
    {
        $data = $this->reader->read($length);
        if (strlen($data) !== $length) {
            throw new StringDataFormatException("Failed to read string: expected length " . $length . ", got " . strlen($data));
        }
        return $data;
    }
}
