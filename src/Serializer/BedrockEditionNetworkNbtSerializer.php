<?php

declare(strict_types=1);

namespace Aternos\Nbt\Serializer;

use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\IO\Binary;

class BedrockEditionNetworkNbtSerializer extends BedrockEditionNbtSerializer
{
    public function getFormat(): NbtFormat
    {
        return NbtFormat::BEDROCK_EDITION_NETWORK;
    }

    /**
     * @inheritDoc
     */
    public function writeLengthPrefix(int $value): static
    {
        $this->getWriter()->write(Binary::writeVarInt($value));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function writeStringLengthPrefix(int $value): static
    {
        $this->getWriter()->write(Binary::writeUnsignedVarInt($value));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function writeInt(int $value): static
    {
        $this->getWriter()->write(Binary::writeVarInt($value));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function writeLong(int $value): static
    {
        $this->getWriter()->write(Binary::writeVarLong($value));
        return $this;
    }
}
