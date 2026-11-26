<?php

declare(strict_types=1);

namespace Aternos\Nbt\Deserializer;

use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\String\JavaEncoding;
use Override;
use pocketmine\utils\Binary;

class JavaEditionNbtDeserializer extends NbtDeserializer
{
    #[Override]
    public function getFormat(): NbtFormat
    {
        return NbtFormat::JavaEdition;
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
        return new DeserializerIntReadResult(Binary::readShort($raw), $raw);
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
        return new DeserializerIntReadResult(Binary::readSignedShort($raw), $raw);
    }

    #[Override]
    public function readInt(): DeserializerIntReadResult
    {
        $raw = $this->reader->read(4);
        return new DeserializerIntReadResult(Binary::readInt($raw), $raw);
    }

    #[Override]
    public function readLong(): DeserializerIntReadResult
    {
        $raw = $this->reader->read(8);
        return new DeserializerIntReadResult(Binary::readLong($raw), $raw);
    }

    #[Override]
    public function readFloat(): DeserializerFloatReadResult
    {
        $raw = $this->reader->read(4);
        return new DeserializerFloatReadResult(Binary::readFloat($raw), $raw);
    }

    #[Override]
    public function readDouble(): DeserializerFloatReadResult
    {
        $raw = $this->reader->read(8);
        return new DeserializerFloatReadResult(Binary::readDouble($raw), $raw);
    }

    #[Override]
    protected function decodeString(string $data): string
    {
        return JavaEncoding::decode($data);
    }
}
