<?php

namespace Aternos\Nbt\Serializer;

use Aternos\Nbt\MachineByteOrder;
use Aternos\Nbt\NbtFormat;
use pocketmine\utils\Binary;

class BedrockEditionNbtSerializer extends NbtSerializer
{
    /**
     * @inheritDoc
     */
    public function getFormat(): int
    {
        return NbtFormat::BEDROCK_EDITION;
    }

    /**
     * @inheritDoc
     */
    public function writeLengthPrefix(int $value): static
    {
        $this->getWriter()->write(Binary::writeLInt(Binary::unsignInt($value)));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function writeStringLengthPrefix(int $value): static
    {
        $this->getWriter()->write(Binary::writeLShort($value));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function writeByte(int $value): static
    {
        $this->getWriter()->write(Binary::writeByte(Binary::unsignByte($value)));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function writeShort(int $value): static
    {
        $this->getWriter()->write(Binary::writeLShort(Binary::unsignShort($value)));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function writeInt(int $value): static
    {
        $this->getWriter()->write(Binary::writeLInt(Binary::unsignInt($value)));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function writeLong(int $value): static
    {
        $packed = pack("q", $value);
        $this->getWriter()->write(MachineByteOrder::isBigEndian() ? strrev($packed) : $packed);
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
