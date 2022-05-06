<?php

namespace Aternos\Nbt;

class MachineByteOrder
{
    protected static ?bool $isLittle = null;

    /**
     * Test whether the machine byte order is little endian or big endian
     */
    protected static function testByteOrder(): void
    {
        if (is_null(static::$isLittle)) {
            static::$isLittle = pack("S", 0x1234) === hex2bin("3412");
        }
    }

    /**
     * @return bool
     */
    public static function isLittleEndian(): bool
    {
        static::testByteOrder();
        return static::$isLittle;
    }

    /**
     * @return bool
     */
    public static function isBigEndian(): bool
    {
        static::testByteOrder();
        return !static::$isLittle;
    }
}
