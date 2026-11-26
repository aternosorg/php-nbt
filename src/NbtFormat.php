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
    case JavaEdition;

    /**
     * Little endian, strings in UTF-8
     */
    case BedrockEdition;

    /**
     * Little endian with VarInt encoded ints, longs and length prefixes, strings in UTF-8
     */
    case BedrockEditionNetwork;

    public function createDeserializer(Reader $reader): NbtDeserializer
    {
        return match ($this) {
            self::JavaEdition => new JavaEditionNbtDeserializer($reader),
            self::BedrockEdition => new BedrockEditionNbtDeserializer($reader),
            self::BedrockEditionNetwork => new BedrockEditionNetworkNbtDeserializer($reader),
        };
    }

    public function createSerializer(Writer $writer): NbtSerializer
    {
        return match ($this) {
            self::JavaEdition => new JavaEditionNbtSerializer($writer),
            self::BedrockEdition => new BedrockEditionNbtSerializer($writer),
            self::BedrockEditionNetwork => new BedrockEditionNetworkNbtSerializer($writer),
        };
    }

    /**
     * Whether a ListTag may be the root tag of an NBT file in this format
     */
    public function allowsListAsRoot(): bool
    {
        return $this !== self::JavaEdition;
    }
}
