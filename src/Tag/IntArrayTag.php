<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\Deserializer\DeserializerIntReadResult;
use Aternos\Nbt\Deserializer\NbtDeserializer;
use Aternos\Nbt\Serializer\NbtSerializer;
use Override;

class IntArrayTag extends NumberArrayTag
{
    public const TagType TYPE = TagType::INT_ARRAY;

    #[Override]
    public function getType(): TagType
    {
        return self::TYPE;
    }

    #[Override]
    protected static function getEntryTagClass(): string
    {
        return IntTag::class;
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
        $values = array_map(strval(...), $this->valueArray);
        return "[I;" . implode(", ", $values) . "]";
    }
}
