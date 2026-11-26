<?php

declare(strict_types=1);

namespace Aternos\Nbt\Serializer;

use Aternos\Nbt\MachineByteOrder;
use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\String\JavaEncoding;
use Aternos\Nbt\IO\Binary;
use Override;

class JavaEditionNbtSerializer extends NbtSerializer
{
    #[Override]
    public function getFormat(): int
    {
        return NbtFormat::JAVA_EDITION;
    }

    #[Override]
    public function writeLengthPrefix(int $value): static
    {
        $this->getWriter()->write(Binary::writeInt($value));
        return $this;
    }

    #[Override]
    public function writeStringLengthPrefix(int $value): static
    {
        $this->checkStringLength($value, 0xffff);
        $this->getWriter()->write(Binary::writeShort($value));
        return $this;
    }

    #[Override]
    public function writeByte(int $value): static
    {
        $this->getWriter()->write(Binary::writeByte($value));
        return $this;
    }

    #[Override]
    public function writeShort(int $value): static
    {
        $this->getWriter()->write(Binary::writeShort($value));
        return $this;
    }

    #[Override]
    public function writeInt(int $value): static
    {
        $this->getWriter()->write(Binary::writeInt($value));
        return $this;
    }

    #[Override]
    public function writeLong(int $value): static
    {
        $packed = pack("q", $value);
        $this->getWriter()->write(MachineByteOrder::isLittleEndian() ? strrev($packed) : $packed);
        return $this;
    }

    #[Override]
    public function writeFloat(float $value): static
    {
        $this->getWriter()->write(Binary::writeFloat($value));
        return $this;
    }

    #[Override]
    public function writeDouble(float $value): static
    {
        $this->getWriter()->write(Binary::writeDouble($value));
        return $this;
    }

    #[Override]
    public function writeString(string $value): static
    {
        $encoded = JavaEncoding::getInstance()->encode($value);
        $this->writeStringLengthPrefix(strlen($encoded));
        $this->getWriter()->write($encoded);
        return $this;
    }
}
