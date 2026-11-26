<?php

declare(strict_types=1);

namespace Aternos\Nbt\Serializer;

use Aternos\Nbt\NbtFormat;
use Override;
use pocketmine\utils\Binary;

class BedrockEditionNbtSerializer extends NbtSerializer
{
    #[Override]
    public function getFormat(): NbtFormat
    {
        return NbtFormat::BEDROCK_EDITION;
    }

    #[Override]
    public function writeLengthPrefix(int $value): static
    {
        return $this->writeInt($value);
    }

    #[Override]
    public function writeStringLengthPrefix(int $value): static
    {
        $this->writer->write(Binary::writeLShort($value));
        return $this;
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
        $this->writer->write(Binary::writeLShort($value));
        return $this;
    }

    #[Override]
    public function writeInt(int $value): static
    {
        $this->writer->write(Binary::writeLInt($value));
        return $this;
    }

    #[Override]
    public function writeLong(int $value): static
    {
        $this->writer->write(Binary::writeLLong($value));
        return $this;
    }

    #[Override]
    public function writeFloat(float $value): static
    {
        $this->writer->write(Binary::writeLFloat($value));
        return $this;
    }

    #[Override]
    public function writeDouble(float $value): static
    {
        $this->writer->write(Binary::writeLDouble($value));
        return $this;
    }

    /**
     * Bedrock Edition strings are plain UTF-8
     */
    #[Override]
    public function encodeString(string $value): string
    {
        return $value;
    }
}
