<?php

declare(strict_types=1);

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

/**
 * The three flavors of the NBT format
 */
enum NbtFormat
{
    /**
     * Big endian, strings in Java's modified UTF-8
     */
    case JAVA_EDITION;

    /**
     * Little endian, strings in UTF-8
     */
    case BEDROCK_EDITION;

    /**
     * Little endian with VarInt encoded ints, longs and length prefixes, strings in UTF-8
     */
    case BEDROCK_EDITION_NETWORK;

    public function createDeserializer(Reader $reader): NbtDeserializer
    {
        return match ($this) {
            self::JAVA_EDITION => new JavaEditionNbtDeserializer($reader),
            self::BEDROCK_EDITION => new BedrockEditionNbtDeserializer($reader),
            self::BEDROCK_EDITION_NETWORK => new BedrockEditionNetworkNbtDeserializer($reader),
        };
    }

    public function createSerializer(Writer $writer): NbtSerializer
    {
        return match ($this) {
            self::JAVA_EDITION => new JavaEditionNbtSerializer($writer),
            self::BEDROCK_EDITION => new BedrockEditionNbtSerializer($writer),
            self::BEDROCK_EDITION_NETWORK => new BedrockEditionNetworkNbtSerializer($writer),
        };
    }

    /**
     * Whether a ListTag may be the root tag of an NBT file in this format
     */
    public function allowsListAsRoot(): bool
    {
        return $this !== self::JAVA_EDITION;
    }
}
