<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\Deserializer\DeserializerIntReadResult;
use Aternos\Nbt\Deserializer\NbtDeserializer;
use Aternos\Nbt\Serializer\NbtSerializer;

class IntArrayTag extends NumberArrayTag
{
    public const TagType TYPE = TagType::INT_ARRAY;

    protected static function getEntryTagClass(): string
    {
        return IntTag::class;
    }

    protected static function readNumber(NbtDeserializer $deserializer): DeserializerIntReadResult
    {
        return $deserializer->readInt();
    }

    protected static function writeNumber(NbtSerializer $serializer, int $value): void
    {
        $serializer->writeInt($value);
    }

    /**
     * @inheritDoc
     */
    public function toSNBT(): string
    {
        $values = [];
        foreach ($this->valueArray as $val) {
            $values[] = $val;
        }
        return "[I;" . implode(", ", $values) . "]";
    }
}
