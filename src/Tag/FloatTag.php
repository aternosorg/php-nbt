<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\Deserializer\DeserializerFloatReadResult;
use Aternos\Nbt\Deserializer\NbtDeserializer;
use Aternos\Nbt\Serializer\NbtSerializer;

class FloatTag extends FloatValueTag
{
    public const TagType TYPE = TagType::FLOAT;

    /**
     * @inheritDoc
     */
    protected static function readNumber(NbtDeserializer $deserializer): DeserializerFloatReadResult
    {
        return $deserializer->readFloat();
    }

    /**
     * @inheritDoc
     */
    protected static function writeNumber(NbtSerializer $serializer, float $value): void
    {
        $serializer->writeFloat($value);
    }

    /**
     * @inheritDoc
     */
    public function toSNBT(): string
    {
        return $this->value . "f";
    }
}
