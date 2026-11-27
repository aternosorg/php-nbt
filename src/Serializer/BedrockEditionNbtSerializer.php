<?php

declare(strict_types=1);

namespace Aternos\Nbt\Serializer;

use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\IO\Binary;

class BedrockEditionNbtSerializer extends NbtSerializer
{
    public function getFormat(): NbtFormat
    {
        return NbtFormat::BEDROCK_EDITION;
    }

    /**
     * @inheritDoc
     */
    public function writeLengthPrefix(int $value): static
    {
        $this->getWriter()->write(Binary::writeLInt($value));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function writeStringLengthPrefix(int $value): static
    {
        $this->checkStringLength($value, 0xffff);
        $this->getWriter()->write(Binary::writeLShort($value));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function writeByte(int $value): static
    {
        $this->getWriter()->write(Binary::writeByte($value));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function writeShort(int $value): static
    {
        $this->getWriter()->write(Binary::writeLShort($value));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function writeInt(int $value): static
    {
        $this->getWriter()->write(Binary::writeLInt($value));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function writeLong(int $value): static
    {
        $this->getWriter()->write(Binary::writeLLong($value));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function writeFloat(float $value): static
    {
        $this->getWriter()->write(Binary::writeLFloat($value));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function writeDouble(float $value): static
    {
        $this->getWriter()->write(Binary::writeLDouble($value));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function writeString(string $value): static
    {
        $this->writeStringLengthPrefix(strlen($value));
        $this->getWriter()->write($value);
        return $this;
    }
}
