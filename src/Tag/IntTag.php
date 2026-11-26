<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\Deserializer\DeserializerIntReadResult;
use Aternos\Nbt\Deserializer\NbtDeserializer;
use Aternos\Nbt\Serializer\NbtSerializer;
use Override;

class IntTag extends IntValueTag
{
    public const TagType TYPE = TagType::INT;
    public const int MIN_VALUE = -0x80000000;
    public const int MAX_VALUE = 0x7fffffff;

    #[Override]
    public function getType(): TagType
    {
        return self::TYPE;
    }

    #[Override]
    protected static function readNumber(NbtDeserializer $deserializer): DeserializerIntReadResult
    {
        return $deserializer->readInt();
    }

    #[Override]
    protected static function writeNumber(NbtSerializer $serializer, int $value): void
    {
        $serializer->writeInt($value);
    }

    #[Override]
    public function toSNBT(): string
    {
        return (string)$this->value;
    }
}
