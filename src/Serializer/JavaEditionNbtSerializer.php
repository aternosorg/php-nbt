<?php

declare(strict_types=1);

namespace Aternos\Nbt\Serializer;

use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\String\JavaEncoding;
use Override;
use pocketmine\utils\Binary;

class JavaEditionNbtSerializer extends NbtSerializer
{
    #[Override]
    public function getFormat(): NbtFormat
    {
        return NbtFormat::JavaEdition;
    }

    #[Override]
    public function writeLengthPrefix(int $value): static
    {
        return $this->writeInt($value);
    }

    #[Override]
    public function writeStringLengthPrefix(int $value): static
    {
        return $this->writeShort($value);
    }

    #[Override]
    public function writeByte(int $value): static
    {
        $this->writer->write(Binary::writeByte($value));
        return $this;
    }

    #[Override]
    public function writeShort(int $value): static
    {
        $this->writer->write(Binary::writeShort($value));
        return $this;
    }

    #[Override]
    public function writeInt(int $value): static
    {
        $this->writer->write(Binary::writeInt($value));
        return $this;
    }

    #[Override]
    public function writeLong(int $value): static
    {
        $this->writer->write(Binary::writeLong($value));
        return $this;
    }

    #[Override]
    public function writeFloat(float $value): static
    {
        $this->writer->write(Binary::writeFloat($value));
        return $this;
    }

    #[Override]
    public function writeDouble(float $value): static
    {
        $this->writer->write(Binary::writeDouble($value));
        return $this;
    }

    #[Override]
    public function encodeString(string $value): string
    {
        return JavaEncoding::encode($value);
    }
}
