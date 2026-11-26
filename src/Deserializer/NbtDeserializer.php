<?php

declare(strict_types=1);

namespace Aternos\Nbt\Deserializer;

use Aternos\Nbt\IO\Reader\Reader;
use Exception;

abstract class NbtDeserializer
{
    public function __construct(protected Reader $reader)
    {
    }

    /**
     * Read an NBT length prefix (TAG_List, TAG_Byte_Array, TAG_Int_Array, and TAG_Long_Array)
     *
     * @return DeserializerIntReadResult
     */
    abstract public function readLengthPrefix(): DeserializerIntReadResult;

    /**
     * Read an NBT string length prefix
     *
     * @return DeserializerIntReadResult
     */
    abstract public function readStringLengthPrefix(): DeserializerIntReadResult;

    /**
     * @return DeserializerIntReadResult
     */
    abstract public function readByte(): DeserializerIntReadResult;

    /**
     * @return DeserializerIntReadResult
     */
    abstract public function readShort(): DeserializerIntReadResult;

    /**
     * @return DeserializerIntReadResult
     */
    abstract public function readInt(): DeserializerIntReadResult;

    /**
     * @return DeserializerIntReadResult
     * @throws Exception if fewer than 8 bytes are left
     */
    abstract public function readLong(): DeserializerIntReadResult;

    /**
     * Read the 8 raw bytes of a long
     *
     * @return string
     * @throws Exception if fewer than 8 bytes are left
     */
    protected function readRawLong(): string
    {
        $raw = $this->getReader()->read(8);
        if (strlen($raw) !== 8) {
            throw new Exception("Failed to read long: expected 8 bytes, got " . strlen($raw));
        }
        return $raw;
    }

    /**
     * @return DeserializerFloatReadResult
     */
    abstract public function readFloat(): DeserializerFloatReadResult;

    /**
     * @return DeserializerFloatReadResult
     */
    abstract public function readDouble(): DeserializerFloatReadResult;

    /**
     * @return DeserializerStringReadResult
     */
    abstract public function readString(): DeserializerStringReadResult;

    /**
     * @return int
     */
    abstract public function getFormat(): int;

    /**
     * @return Reader
     */
    public function getReader(): Reader
    {
        return $this->reader;
    }
}
