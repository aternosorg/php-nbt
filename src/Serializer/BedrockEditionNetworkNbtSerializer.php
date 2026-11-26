<?php

declare(strict_types=1);

namespace Aternos\Nbt\Serializer;

use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\IO\Binary;
use Override;

class BedrockEditionNetworkNbtSerializer extends BedrockEditionNbtSerializer
{
    #[Override]
    public function getFormat(): int
    {
        return NbtFormat::BEDROCK_EDITION_NETWORK;
    }

    #[Override]
    public function writeLengthPrefix(int $value): static
    {
        $this->getWriter()->write(Binary::writeVarInt($value));
        return $this;
    }

    #[Override]
    public function writeStringLengthPrefix(int $value): static
    {
        $this->getWriter()->write(Binary::writeUnsignedVarInt($value));
        return $this;
    }

    #[Override]
    public function writeInt(int $value): static
    {
        $this->getWriter()->write(Binary::writeVarInt($value));
        return $this;
    }

    #[Override]
    public function writeLong(int $value): static
    {
        $this->getWriter()->write(Binary::writeVarLong($value));
        return $this;
    }
}
