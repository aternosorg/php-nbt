<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\Deserializer\DeserializerIntReadResult;
use Aternos\Nbt\Deserializer\NbtDeserializer;
use Aternos\Nbt\Serializer\NbtSerializer;

class ByteArrayTag extends NumberArrayTag
{
    public const TagType TYPE = TagType::BYTE_ARRAY;

    protected static function getEntryTagClass(): string
    {
        return ByteTag::class;
    }

    protected static function readNumber(NbtDeserializer $deserializer): DeserializerIntReadResult
    {
        return $deserializer->readByte();
    }

    protected static function writeNumber(NbtSerializer $serializer, int $value): void
    {
        $serializer->writeByte($value);
    }

    /**
     * @inheritDoc
     */
    protected function getValueString(): string
    {
        $values = array_map(
            fn(int $byte): string => sprintf("%02x", $byte & 0xff),
            array_slice($this->valueArray, 0, 32)
        );
        if (count($this->valueArray) > 32) {
            $values[] = "...";
        }
        return $this->count() . " byte" . ($this->count() === 1 ? "" : "s") . " [" . implode(" ", $values) . "]";
    }

    /**
     * @inheritDoc
     */
    public function toSNBT(): string
    {
        $values = [];
        foreach ($this->valueArray as $val) {
            $values[] = $val . "b";
        }
        return "[B;" . implode(", ", $values) . "]";
    }
}
