<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\Deserializer\DeserializerIntReadResult;
use Aternos\Nbt\Deserializer\NbtDeserializer;
use Aternos\Nbt\Serializer\NbtSerializer;
use Override;

class LongTag extends IntValueTag
{
    public const TagType TYPE = TagType::LONG;

    #[Override]
    public function getType(): TagType
    {
        return self::TYPE;
    }

    #[Override]
    protected static function readNumber(NbtDeserializer $deserializer): DeserializerIntReadResult
    {
        return $deserializer->readLong();
    }

    #[Override]
    protected static function writeNumber(NbtSerializer $serializer, int $value): void
    {
        $serializer->writeLong($value);
    }

    #[Override]
    public function toSNBT(): string
    {
        return $this->value . "L";
    }
}
