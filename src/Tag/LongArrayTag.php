<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\Deserializer\DeserializerIntReadResult;
use Aternos\Nbt\Deserializer\NbtDeserializer;
use Aternos\Nbt\Serializer\NbtSerializer;
use Override;

class LongArrayTag extends NumberArrayTag
{
    public const TagType TYPE = TagType::LONG_ARRAY;

    #[Override]
    public function getType(): TagType
    {
        return self::TYPE;
    }

    #[Override]
    protected static function getEntryTagClass(): string
    {
        return LongTag::class;
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
        $values = array_map(fn(int $value): string => $value . "L", $this->valueArray);
        return "[L;" . implode(", ", $values) . "]";
    }
}
