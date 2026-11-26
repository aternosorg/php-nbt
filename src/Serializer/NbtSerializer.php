<?php

namespace Aternos\Nbt\Serializer;

use Aternos\Nbt\IO\Writer\Writer;
use Exception;

abstract class NbtSerializer
{
    public function __construct(protected Writer $writer)
    {
    }

    /**
     * Write an NBT length prefix (TAG_List, TAG_Byte_Array, TAG_Int_Array, and TAG_Long_Array)
     *
     * @param int $value
     * @return $this
     */
    abstract public function writeLengthPrefix(int $value): static;

    /**
     * @param int $length length of the encoded string in bytes
     * @param int $maxLength
     * @return void
     * @throws Exception
     */
    protected function checkStringLength(int $length, int $maxLength): void
    {
        if ($length > $maxLength) {
            throw new Exception("String of " . $length . " bytes exceeds maximum length of " . $maxLength . " bytes");
        }
    }

    /**
     * Write an NBT string length prefix
     *
     * @param int $value
     * @return $this
     * @throws Exception if the length does not fit into the prefix
     */
    abstract public function writeStringLengthPrefix(int $value): static;

    /**
     * @param int $value
     * @return $this
     */
    abstract public function writeByte(int $value): static;

    /**
     * @param int $value
     * @return $this
     */
    abstract public function writeShort(int $value): static;

    /**
     * @param int $value
     * @return $this
     */
    abstract public function writeInt(int $value): static;

    /**
     * @param int $value
     * @return $this
     */
    abstract public function writeLong(int $value): static;

    /**
     * @param float $value
     * @return $this
     */
    abstract public function writeFloat(float $value): static;

    /**
     * @param float $value
     * @return $this
     */
    abstract public function writeDouble(float $value): static;

    /**
     * @param string $value
     * @return $this
     */
    abstract public function writeString(string $value): static;

    /**
     * @return int
     */
    abstract public function getFormat(): int;

    /**
     * @return Writer
     */
    public function getWriter(): Writer
    {
        return $this->writer;
    }
}
