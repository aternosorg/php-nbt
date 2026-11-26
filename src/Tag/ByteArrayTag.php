<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\Deserializer\DeserializerIntReadResult;
use Aternos\Nbt\Deserializer\NbtDeserializer;
use Aternos\Nbt\Serializer\NbtSerializer;
use Override;

class ByteArrayTag extends NumberArrayTag
{
    public const TagType TYPE = TagType::BYTE_ARRAY;

    #[Override]
    public function getType(): TagType
    {
        return self::TYPE;
    }

    #[Override]
    protected static function getEntryTagClass(): string
    {
        return ByteTag::class;
    }

    #[Override]
    protected static function readNumber(NbtDeserializer $deserializer): DeserializerIntReadResult
    {
        return $deserializer->readByte();
    }

    #[Override]
    protected static function writeNumber(NbtSerializer $serializer, int $value): void
    {
        $serializer->writeByte($value);
    }

    #[Override]
    protected function getValueString(): string
    {
        $count = $this->count();
        $hexValues = array_map(
            fn(int $byte): string => sprintf("%02x", $byte & 0xff),
            array_slice($this->valueArray, 0, 32)
        );
        if ($count > 32) {
            $hexValues[] = "...";
        }
        return $count . " byte" . ($count === 1 ? "" : "s") . " [" . implode(" ", $hexValues) . "]";
    }

    #[Override]
    public function toSNBT(): string
    {
        $values = array_map(fn(int $value): string => $value . "b", $this->valueArray);
        return "[B;" . implode(", ", $values) . "]";
    }
}
