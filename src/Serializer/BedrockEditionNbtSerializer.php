<?php

declare(strict_types=1);

namespace Aternos\Nbt\Serializer;

use Aternos\Nbt\MachineByteOrder;
use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\IO\Binary;
use Override;

class BedrockEditionNbtSerializer extends NbtSerializer
{
    #[Override]
    public function getFormat(): int
    {
        return NbtFormat::BEDROCK_EDITION;
    }

    #[Override]
    public function writeLengthPrefix(int $value): static
    {
        $this->getWriter()->write(Binary::writeLInt($value));
        return $this;
    }

    #[Override]
    public function writeStringLengthPrefix(int $value): static
    {
        $this->checkStringLength($value, 0xffff);
        $this->getWriter()->write(Binary::writeLShort($value));
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
        $this->getWriter()->write(Binary::writeLShort($value));
        return $this;
    }

    #[Override]
    public function writeInt(int $value): static
    {
        $this->getWriter()->write(Binary::writeLInt($value));
        return $this;
    }

    #[Override]
    public function writeLong(int $value): static
    {
        $packed = pack("q", $value);
        $this->getWriter()->write(MachineByteOrder::isBigEndian() ? strrev($packed) : $packed);
        return $this;
    }

    #[Override]
    public function writeFloat(float $value): static
    {
        $this->getWriter()->write(Binary::writeLFloat($value));
        return $this;
    }

    #[Override]
    public function writeDouble(float $value): static
    {
        $this->getWriter()->write(Binary::writeLDouble($value));
        return $this;
    }

    #[Override]
    public function writeString(string $value): static
    {
        $this->writeStringLengthPrefix(strlen($value));
        $this->getWriter()->write($value);
        return $this;
    }
}
