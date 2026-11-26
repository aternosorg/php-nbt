<?php

declare(strict_types=1);

namespace Aternos\Nbt\Serializer;

use Aternos\Nbt\Exception\InvalidTagValueException;
use Aternos\Nbt\Exception\StringDataFormatException;
use Aternos\Nbt\IO\Writer\Writer;
use Aternos\Nbt\NbtFormat;

abstract class NbtSerializer
{
    /**
     * Maximum length of an encoded string in bytes
     */
    public const int MAX_STRING_LENGTH = 0xffff;

    public readonly Writer $writer;

    public function __construct(Writer $writer)
    {
        $this->writer = $writer;
    }

    abstract public function getFormat(): NbtFormat;

    /**
     * Write an NBT length prefix (TAG_List, TAG_Byte_Array, TAG_Int_Array, and TAG_Long_Array)
     */
    abstract public function writeLengthPrefix(int $value): static;

    /**
     * Write an NBT string length prefix
     */
    abstract public function writeStringLengthPrefix(int $value): static;

    abstract public function writeByte(int $value): static;

    abstract public function writeShort(int $value): static;

    abstract public function writeInt(int $value): static;

    abstract public function writeLong(int $value): static;

    abstract public function writeFloat(float $value): static;

    abstract public function writeDouble(float $value): static;

    /**
     * Convert a UTF-8 string to the format's string encoding
     *
     * @throws StringDataFormatException
     */
    abstract public function encodeString(string $value): string;

    /**
     * Write a UTF-8 string with its length prefix
     *
     * @throws InvalidTagValueException if the encoded string is longer than MAX_STRING_LENGTH bytes
     * @throws StringDataFormatException
     */
    public function writeString(string $value): static
    {
        $encoded = $this->encodeString($value);
        if (strlen($encoded) > static::MAX_STRING_LENGTH) {
            throw new InvalidTagValueException("String exceeds maximum length of " . static::MAX_STRING_LENGTH . " bytes: " . strlen($encoded) . " bytes");
        }
        $this->writeStringLengthPrefix(strlen($encoded));
        $this->writer->write($encoded);
        return $this;
    }
}
