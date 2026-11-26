<?php

declare(strict_types=1);

namespace Aternos\Nbt\Deserializer;

use Aternos\Nbt\NbtFormat;
use Override;
use pocketmine\utils\Binary;

class BedrockEditionNbtDeserializer extends NbtDeserializer
{
    #[Override]
    public function getFormat(): NbtFormat
    {
        return NbtFormat::BEDROCK_EDITION;
    }

    #[Override]
    public function readLengthPrefix(): DeserializerIntReadResult
    {
        return $this->readInt();
    }

    #[Override]
    public function readStringLengthPrefix(): DeserializerIntReadResult
    {
        $raw = $this->reader->read(2);
        return new DeserializerIntReadResult(Binary::readLShort($raw), $raw);
    }

    #[Override]
    public function readByte(): DeserializerIntReadResult
    {
        $raw = $this->reader->read(1);
        return new DeserializerIntReadResult(Binary::readSignedByte($raw), $raw);
    }

    #[Override]
    public function readShort(): DeserializerIntReadResult
    {
        $raw = $this->reader->read(2);
        return new DeserializerIntReadResult(Binary::readSignedLShort($raw), $raw);
    }

    #[Override]
    public function readInt(): DeserializerIntReadResult
    {
        $raw = $this->reader->read(4);
        return new DeserializerIntReadResult(Binary::readLInt($raw), $raw);
    }

    #[Override]
    public function readLong(): DeserializerIntReadResult
    {
        $raw = $this->reader->read(8);
        return new DeserializerIntReadResult(Binary::readLLong($raw), $raw);
    }

    #[Override]
    public function readFloat(): DeserializerFloatReadResult
    {
        $raw = $this->reader->read(4);
        return new DeserializerFloatReadResult(Binary::readLFloat($raw), $raw);
    }

    #[Override]
    public function readDouble(): DeserializerFloatReadResult
    {
        $raw = $this->reader->read(8);
        return new DeserializerFloatReadResult(Binary::readLDouble($raw), $raw);
    }

    /**
     * Bedrock Edition strings are plain UTF-8
     */
    #[Override]
    protected function decodeString(string $data): string
    {
        return $data;
    }
}
