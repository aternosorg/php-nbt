<?php

namespace Aternos\Nbt\Serializer;

use Aternos\Nbt\IO\Reader\Reader;

interface NbtSerializer
{
    /**
     * Read a NBT length prefix (TAG_List, TAG_Byte_Array, TAG_Int_Array, and TAG_Long_Array)
     *
     * @param Reader $reader
     * @return SerializerReadResult
     */
    public function readLengthPrefix(Reader $reader): SerializerReadResult;

    /**
     * Encode a NBT length prefix (TAG_List, TAG_Byte_Array, TAG_Int_Array, and TAG_Long_Array)
     *
     * @param int $value
     * @return string
     */
    public function encodeLengthPrefix(int $value): string;

    /**
     * Read a NBT string length prefix
     *
     * @param Reader $reader
     * @return SerializerReadResult
     */
    public function readStringLengthPrefix(Reader $reader): SerializerReadResult;

    /**
     * Encode a NBT string length prefix
     *
     * @param int $value
     * @return string
     */
    public function encodeStringLengthPrefix(int $value): string;

    /**
     * Encode a byte
     *
     * @param string $data
     * @return int
     */
    public function decodeByte(string $data): int;

    /**
     * Decode a byte
     *
     * @param int $value
     * @return string
     */
    public function encodeByte(int $value): string;

    /**
     * Encode a short
     *
     * @param string $data
     * @return int
     */
    public function decodeShort(string $data): int;

    /**
     * Decode a short
     *
     * @param int $value
     * @return string
     */
    public function encodeShort(int $value): string;

    /**
     * Decode an int
     *
     * @param int $value
     * @return string
     */
    public function encodeInt(int $value): string;

    /**
     * Read an integer from a Reader object
     *
     * @param Reader $reader
     * @return SerializerReadResult
     */
    public function readInt(Reader $reader): SerializerReadResult;

    /**
     * Decode a long
     *
     * @param int $value
     * @return string
     */
    public function encodeLong(int $value): string;

    /**
     * Read a long from a Reader object
     *
     * @param Reader $reader
     * @return SerializerReadResult
     */
    public function readLong(Reader $reader): SerializerReadResult;

    /**
     * Encode a float
     *
     * @param string $data
     * @return float
     */
    public function decodeFloat(string $data): float;

    /**
     * Decode a float
     *
     * @param float $value
     * @return string
     */
    public function encodeFloat(float $value): string;

    /**
     * Encode a double
     *
     * @param string $data
     * @return float
     */
    public function decodeDouble(string $data): float;

    /**
     * Decode a double
     *
     * @param float $value
     * @return string
     */
    public function encodeDouble(float $value): string;

    /**
     * @return int
     */
    public function getFormat(): int;
}