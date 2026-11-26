<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\Deserializer\DeserializerIntReadResult;
use Aternos\Nbt\Deserializer\NbtDeserializer;
use Aternos\Nbt\Serializer\NbtSerializer;
use Override;

class ByteTag extends IntValueTag
{
    public const TagType TYPE = TagType::Byte;
    public const int MIN_VALUE = -0x80;
    public const int MAX_VALUE = 0x7f;

    #[Override]
    public function getType(): TagType
    {
        return self::TYPE;
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
    public function toSNBT(): string
    {
        return $this->value . "b";
    }
}
