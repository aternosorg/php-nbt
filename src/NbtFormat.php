<?php

namespace Aternos\Nbt;

use Aternos\Nbt\Deserializer\BedrockEditionNbtDeserializer;
use Aternos\Nbt\Deserializer\BedrockEditionNetworkNbtDeserializer;
use Aternos\Nbt\Deserializer\JavaEditionNbtDeserializer;
use Aternos\Nbt\Deserializer\NbtDeserializer;
use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Aternos\Nbt\Serializer\BedrockEditionNbtSerializer;
use Aternos\Nbt\Serializer\BedrockEditionNetworkNbtSerializer;
use Aternos\Nbt\Serializer\JavaEditionNbtSerializer;
use Aternos\Nbt\Serializer\NbtSerializer;

class NbtFormat
{
    const JAVA_EDITION = 0;
    const BEDROCK_EDITION = 1;
    const BEDROCK_EDITION_NETWORK = 2;

    /**
     * Find the appropriate deserializer for an NBT format
     *
     * @param int $type
     * @param Reader $reader
     * @return NbtDeserializer
     */
    public static function getDeserializer(int $type, Reader $reader): NbtDeserializer
    {
        return match ($type) {
            static::BEDROCK_EDITION => new BedrockEditionNbtDeserializer($reader),
            static::BEDROCK_EDITION_NETWORK => new BedrockEditionNetworkNbtDeserializer($reader),
            default => new JavaEditionNbtDeserializer($reader),
        };
    }

    /**
     * Find the appropriate serializer for an NBT format
     *
     * @param int $type
     * @param Writer $writer
     * @return NbtSerializer
     */
    public static function getSerializer(int $type, Writer $writer): NbtSerializer
    {
        return match ($type) {
            static::BEDROCK_EDITION => new BedrockEditionNbtSerializer($writer),
            static::BEDROCK_EDITION_NETWORK => new BedrockEditionNetworkNbtSerializer($writer),
            default => new JavaEditionNbtSerializer($writer),
        };
    }
}
