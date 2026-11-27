<?php

declare(strict_types=1);

namespace Aternos\Nbt\IO;

/**
 * Encodes and decodes the fixed size and variable length numbers used by the NBT formats
 *
 * Methods without a prefix use big endian byte order, methods prefixed with "L" use little endian byte order.
 * All read methods expect the value at the start of the given string and ignore any bytes after it.
 */
final class Binary
{
    protected const int MAX_VAR_INT_BYTES = 5;
    protected const int MAX_VAR_LONG_BYTES = 10;

    /**
     * Read a signed 8-bit integer
     *
     * @throws BinaryDataException
     */
    public static function readSignedByte(string $data): int
    {
        self::requireLength($data, 1);
        return self::signExtend(ord($data[0]), 8);
    }

    /**
     * Write a signed or unsigned 8-bit integer
     */
    public static function writeByte(int $value): string
    {
        return chr($value & 0xff);
    }

    /**
     * Read an unsigned big endian 16-bit integer
     *
     * @throws BinaryDataException
     */
    public static function readShort(string $data): int
    {
        return self::unpackExact("n", $data, 2);
    }

    /**
     * Read a signed big endian 16-bit integer
     *
     * @throws BinaryDataException
     */
    public static function readSignedShort(string $data): int
    {
        return self::signExtend(self::readShort($data), 16);
    }

    /**
     * Write a signed or unsigned big endian 16-bit integer
     */
    public static function writeShort(int $value): string
    {
        return pack("n", $value);
    }

    /**
     * Read an unsigned little endian 16-bit integer
     *
     * @throws BinaryDataException
     */
    public static function readLShort(string $data): int
    {
        return self::unpackExact("v", $data, 2);
    }

    /**
     * Read a signed little endian 16-bit integer
     *
     * @throws BinaryDataException
     */
    public static function readSignedLShort(string $data): int
    {
        return self::signExtend(self::readLShort($data), 16);
    }

    /**
     * Write a signed or unsigned little endian 16-bit integer
     */
    public static function writeLShort(int $value): string
    {
        return pack("v", $value);
    }

    /**
     * Read a signed big endian 32-bit integer
     *
     * @throws BinaryDataException
     */
    public static function readInt(string $data): int
    {
        return self::signExtend(self::unpackExact("N", $data, 4), 32);
    }

    /**
     * Write a signed or unsigned big endian 32-bit integer
     */
    public static function writeInt(int $value): string
    {
        return pack("N", $value);
    }

    /**
     * Read a signed little endian 32-bit integer
     *
     * @throws BinaryDataException
     */
    public static function readLInt(string $data): int
    {
        return self::signExtend(self::unpackExact("V", $data, 4), 32);
    }

    /**
     * Write a signed or unsigned little endian 32-bit integer
     */
    public static function writeLInt(int $value): string
    {
        return pack("V", $value);
    }

    /**
     * Read a signed big endian 64-bit integer
     *
     * @throws BinaryDataException
     */
    public static function readLong(string $data): int
    {
        // "J" is unsigned, but PHP ints are signed 64-bit, so values above PHP_INT_MAX wrap to negative numbers
        return self::unpackExact("J", $data, 8);
    }

    /**
     * Write a signed big endian 64-bit integer
     */
    public static function writeLong(int $value): string
    {
        return pack("J", $value);
    }

    /**
     * Read a signed little endian 64-bit integer
     *
     * @throws BinaryDataException
     */
    public static function readLLong(string $data): int
    {
        // "P" is unsigned, but PHP ints are signed 64-bit, so values above PHP_INT_MAX wrap to negative numbers
        return self::unpackExact("P", $data, 8);
    }

    /**
     * Write a signed little endian 64-bit integer
     */
    public static function writeLLong(int $value): string
    {
        return pack("P", $value);
    }

    /**
     * Read a big endian 32-bit float
     *
     * @throws BinaryDataException
     */
    public static function readFloat(string $data): float
    {
        return self::unpackExact("G", $data, 4);
    }

    /**
     * Write a big endian 32-bit float
     */
    public static function writeFloat(float $value): string
    {
        return pack("G", $value);
    }

    /**
     * Read a little endian 32-bit float
     *
     * @throws BinaryDataException
     */
    public static function readLFloat(string $data): float
    {
        return self::unpackExact("g", $data, 4);
    }

    /**
     * Write a little endian 32-bit float
     */
    public static function writeLFloat(float $value): string
    {
        return pack("g", $value);
    }

    /**
     * Read a big endian 64-bit float
     *
     * @throws BinaryDataException
     */
    public static function readDouble(string $data): float
    {
        return self::unpackExact("E", $data, 8);
    }

    /**
     * Write a big endian 64-bit float
     */
    public static function writeDouble(float $value): string
    {
        return pack("E", $value);
    }

    /**
     * Read a little endian 64-bit float
     *
     * @throws BinaryDataException
     */
    public static function readLDouble(string $data): float
    {
        return self::unpackExact("e", $data, 8);
    }

    /**
     * Write a little endian 64-bit float
     */
    public static function writeLDouble(float $value): string
    {
        return pack("e", $value);
    }

    /**
     * Read a zigzag encoded variable length 32-bit integer
     *
     * @param int $offset position of the first byte, moved behind the last byte of the value
     * @throws BinaryDataException
     */
    public static function readVarInt(string $data, int &$offset): int
    {
        return self::zigzagDecode(self::readUnsignedVarInt($data, $offset));
    }

    /**
     * Read an unsigned variable length 32-bit integer
     *
     * @param int $offset position of the first byte, moved behind the last byte of the value
     * @throws BinaryDataException
     */
    public static function readUnsignedVarInt(string $data, int &$offset): int
    {
        return self::readVariableLength($data, $offset, self::MAX_VAR_INT_BYTES, "VarInt");
    }

    /**
     * Write a 32-bit integer as zigzag encoded variable length integer
     *
     * Bits above the lower 32 bits are ignored.
     */
    public static function writeVarInt(int $value): string
    {
        $value = self::signExtend($value & 0xffffffff, 32);
        return self::writeUnsignedVarInt(self::zigzagEncode($value));
    }

    /**
     * Write an unsigned 32-bit integer as variable length integer
     *
     * Bits above the lower 32 bits are ignored.
     */
    public static function writeUnsignedVarInt(int $value): string
    {
        return self::writeVariableLength($value & 0xffffffff);
    }

    /**
     * Read a zigzag encoded variable length 64-bit integer
     *
     * @param int $offset position of the first byte, moved behind the last byte of the value
     * @throws BinaryDataException
     */
    public static function readVarLong(string $data, int &$offset): int
    {
        $raw = self::readVariableLength($data, $offset, self::MAX_VAR_LONG_BYTES, "VarLong");
        return self::zigzagDecode($raw);
    }

    /**
     * Write a 64-bit integer as zigzag encoded variable length integer
     */
    public static function writeVarLong(int $value): string
    {
        return self::writeVariableLength(self::zigzagEncode($value));
    }

    /**
     * Map signed to unsigned values so that small negative numbers stay small (0 => 0, -1 => 1, 1 => 2, -2 => 3)
     */
    protected static function zigzagEncode(int $value): int
    {
        return ($value << 1) ^ ($value >> 63);
    }

    /**
     * Reverse of zigzagEncode, works on all 64 bits of the raw value
     */
    protected static function zigzagDecode(int $raw): int
    {
        $withoutSignBit = self::shiftRightUnsigned($raw, 1);
        $sign = -($raw & 1);
        return $withoutSignBit ^ $sign;
    }

    /**
     * Read 7 bits per byte, least significant group first, until a byte without the continuation bit (0x80)
     *
     * @throws BinaryDataException
     */
    protected static function readVariableLength(string $data, int &$offset, int $maxBytes, string $typeName): int
    {
        $value = 0;
        for ($i = 0; $i < $maxBytes; $i++) {
            if (!isset($data[$offset])) {
                throw new BinaryDataException("No bytes left in buffer while reading " . $typeName);
            }
            $byte = ord($data[$offset++]);
            $value |= ($byte & 0x7f) << (7 * $i);

            if (($byte & 0x80) === 0) {
                return $value;
            }
        }
        throw new BinaryDataException($typeName . " did not terminate after " . $maxBytes . " bytes");
    }

    /**
     * Write 7 bits per byte, least significant group first, setting the continuation bit (0x80) on all but the last byte
     *
     * The value is treated as unsigned, so negative values take the maximum of 10 bytes.
     */
    protected static function writeVariableLength(int $value): string
    {
        $result = "";
        while (true) {
            $group = $value & 0x7f;
            $value = self::shiftRightUnsigned($value, 7);
            if ($value === 0) {
                return $result . chr($group);
            }
            $result .= chr($group | 0x80);
        }
    }

    /**
     * Shift right and fill with zeros instead of the sign bit
     */
    protected static function shiftRightUnsigned(int $value, int $bits): int
    {
        return ($value >> $bits) & (PHP_INT_MAX >> ($bits - 1));
    }

    /**
     * Interpret the lower $bits bits of $value as signed integer
     */
    protected static function signExtend(int $value, int $bits): int
    {
        $shift = PHP_INT_SIZE * 8 - $bits;
        return $value << $shift >> $shift;
    }

    /**
     * @throws BinaryDataException
     */
    protected static function requireLength(string $data, int $length): void
    {
        if (strlen($data) < $length) {
            throw new BinaryDataException("Not enough bytes: need " . $length . ", have " . strlen($data));
        }
    }

    /**
     * Unpack a single value after checking that enough bytes are available
     *
     * @return ($format is 'G'|'g'|'E'|'e' ? float : int)
     * @throws BinaryDataException
     */
    protected static function unpackExact(string $format, string $data, int $length): int|float
    {
        self::requireLength($data, $length);
        return unpack($format, $data)[1];
    }
}
