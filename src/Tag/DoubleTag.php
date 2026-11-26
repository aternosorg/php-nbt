<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\Deserializer\DeserializerFloatReadResult;
use Aternos\Nbt\Deserializer\NbtDeserializer;
use Aternos\Nbt\Serializer\NbtSerializer;
use Override;

class DoubleTag extends FloatValueTag
{
    public const TagType TYPE = TagType::DOUBLE;

    #[Override]
    public function getType(): TagType
    {
        return self::TYPE;
    }

    #[Override]
    protected static function readNumber(NbtDeserializer $deserializer): DeserializerFloatReadResult
    {
        return $deserializer->readDouble();
    }

    #[Override]
    protected static function writeNumber(NbtSerializer $serializer, float $value): void
    {
        $serializer->writeDouble($value);
    }

    #[Override]
    public function toSNBT(): string
    {
        return $this->value . "d";
    }
}
