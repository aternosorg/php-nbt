<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\Deserializer\DeserializerIntReadResult;
use Aternos\Nbt\Deserializer\NbtDeserializer;
use Aternos\Nbt\Serializer\NbtSerializer;
use Override;

class ShortTag extends IntValueTag
{
    public const TagType TYPE = TagType::SHORT;
    public const int MIN_VALUE = -0x8000;
    public const int MAX_VALUE = 0x7fff;

    #[Override]
    public function getType(): TagType
    {
        return self::TYPE;
    }

    #[Override]
    protected static function readNumber(NbtDeserializer $deserializer): DeserializerIntReadResult
    {
        return $deserializer->readShort();
    }

    #[Override]
    protected static function writeNumber(NbtSerializer $serializer, int $value): void
    {
        $serializer->writeShort($value);
    }

    #[Override]
    public function toSNBT(): string
    {
        return $this->value . "s";
    }
}
