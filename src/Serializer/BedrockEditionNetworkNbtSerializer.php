<?php

declare(strict_types=1);

namespace Aternos\Nbt\Serializer;

use Aternos\Nbt\NbtFormat;
use Override;
use pocketmine\utils\Binary;

class BedrockEditionNetworkNbtSerializer extends BedrockEditionNbtSerializer
{
    #[Override]
    public function getFormat(): NbtFormat
    {
        return NbtFormat::BEDROCK_EDITION_NETWORK;
    }

    #[Override]
    public function writeLengthPrefix(int $value): static
    {
        return $this->writeInt($value);
    }

    #[Override]
    public function writeStringLengthPrefix(int $value): static
    {
        $this->writer->write(Binary::writeUnsignedVarInt($value));
        return $this;
    }

    #[Override]
    public function writeInt(int $value): static
    {
        $this->writer->write(Binary::writeVarInt($value));
        return $this;
    }

    #[Override]
    public function writeLong(int $value): static
    {
        $this->writer->write(Binary::writeVarLong($value));
        return $this;
    }
}
