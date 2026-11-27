<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tests\IO;

use Aternos\Nbt\IO\Binary;
use Aternos\Nbt\IO\BinaryDataException;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class BinaryTest extends TestCase
{
    #[TestWith(["readSignedByte", "", "Not enough bytes: need 1, have 0"])]
    #[TestWith(["readShort", "\x00", "Not enough bytes: need 2, have 1"])]
    #[TestWith(["readLInt", "\x00\x00", "Not enough bytes: need 4, have 2"])]
    #[TestWith(["readDouble", "\x00\x00\x00", "Not enough bytes: need 8, have 3"])]
    #[TestWith(["readLong", "\x00\x00\x00\x00\x00\x00\x00", "Not enough bytes: need 8, have 7"])]
    #[TestWith(["readLLong", "\x00", "Not enough bytes: need 8, have 1"])]
    public function testFixedSizeReadRejectsShortData(string $method, string $data, string $message): void
    {
        $this->expectException(BinaryDataException::class);
        $this->expectExceptionMessage($message);
        Binary::$method($data);
    }

    #[TestWith([PHP_INT_MIN, "8000000000000000", "0000000000000080"])]
    #[TestWith([PHP_INT_MAX, "7fffffffffffffff", "ffffffffffffff7f"])]
    #[TestWith([-1, "ffffffffffffffff", "ffffffffffffffff"])]
    #[TestWith([0x0102030405060708, "0102030405060708", "0807060504030201"])]
    public function testLongEncodingInBothByteOrders(int $value, string $bigEndianHex, string $littleEndianHex): void
    {
        $this->assertSame($bigEndianHex, bin2hex(Binary::writeLong($value)));
        $this->assertSame($littleEndianHex, bin2hex(Binary::writeLLong($value)));
        $this->assertSame($value, Binary::readLong(hex2bin($bigEndianHex)));
        $this->assertSame($value, Binary::readLLong(hex2bin($littleEndianHex)));
    }

    #[TestWith(["readVarInt", "", "No bytes left in buffer while reading VarInt"])]
    #[TestWith(["readUnsignedVarInt", "\x80\x80", "No bytes left in buffer while reading VarInt"])]
    #[TestWith(["readVarInt", "\xff\xff\xff\xff\xff\x01", "VarInt did not terminate after 5 bytes"])]
    #[TestWith(["readVarLong", "\xff", "No bytes left in buffer while reading VarLong"])]
    #[TestWith(["readVarLong", "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\x01", "VarLong did not terminate after 10 bytes"])]
    public function testVariableLengthReadRejectsInvalidData(string $method, string $data, string $message): void
    {
        $offset = 0;
        $this->expectException(BinaryDataException::class);
        $this->expectExceptionMessage($message);
        Binary::$method($data, $offset);
    }

    /**
     * @throws BinaryDataException
     */
    public function testVariableLengthReadStartsAtOffsetAndMovesItBehindValue(): void
    {
        $offset = 1;
        $this->assertSame(300, Binary::readVarInt("\x00\xd8\x04\x00", $offset));
        $this->assertSame(3, $offset);
    }

    #[TestWith([2147483648, "ffffffff0f"], "wraps to int min")]
    #[TestWith([4294967295, "01"], "wraps to -1")]
    public function testVarIntOnlyUsesLower32Bits(int $value, string $expectedHex): void
    {
        $this->assertSame($expectedHex, bin2hex(Binary::writeVarInt($value)));
    }

    public function testUnsignedVarIntOnlyUsesLower32Bits(): void
    {
        $this->assertSame("00", bin2hex(Binary::writeUnsignedVarInt(4294967296)));
        $this->assertSame("ffffffff0f", bin2hex(Binary::writeUnsignedVarInt(-1)));
    }

    #[TestWith([0])]
    #[TestWith([-1])]
    #[TestWith([1])]
    #[TestWith([PHP_INT_MAX])]
    #[TestWith([PHP_INT_MIN])]
    #[TestWith([PHP_INT_MIN + 1])]
    #[TestWith([4611686018427387904])]
    #[TestWith([-4611686018427387905])]
    public function testVarLongRoundTrip(int $value): void
    {
        $offset = 0;
        $encoded = Binary::writeVarLong($value);
        $this->assertSame($value, Binary::readVarLong($encoded, $offset));
        $this->assertSame(strlen($encoded), $offset);
    }
}
