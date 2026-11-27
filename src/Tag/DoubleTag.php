<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\Deserializer\DeserializerFloatReadResult;
use Aternos\Nbt\Deserializer\NbtDeserializer;
use Aternos\Nbt\Serializer\NbtSerializer;

class DoubleTag extends FloatValueTag
{
    public const TagType TYPE = TagType::DOUBLE;

    /**
     * @inheritDoc
     */
    protected static function readNumber(NbtDeserializer $deserializer): DeserializerFloatReadResult
    {
        return $deserializer->readDouble();
    }

    /**
     * @inheritDoc
     */
    protected static function writeNumber(NbtSerializer $serializer, float $value): void
    {
        $serializer->writeDouble($value);
    }

    /**
     * @inheritDoc
     */
    public function toSNBT(): string
    {
        return $this->value . "d";
    }
}
