<?php

namespace Aternos\Nbt;

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
     * Find the appropriate serializer for an NBT format
     *
     * @param int $type
     * @return NbtSerializer
     */
    public static function getSerializer(int $type): NbtSerializer
    {
        switch ($type) {
            case static::BEDROCK_EDITION:
                return new BedrockEditionNbtSerializer();
            case static::BEDROCK_EDITION_NETWORK:
                return new BedrockEditionNetworkNbtSerializer();
            default:
                return new JavaEditionNbtSerializer();
        }
    }
}