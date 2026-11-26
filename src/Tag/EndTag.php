<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Override;

/**
 * Marks the end of a compound tag, has no name and no payload
 */
class EndTag extends Tag
{
    public const TagType TYPE = TagType::END;

    #[Override]
    public function getType(): TagType
    {
        return self::TYPE;
    }

    #[Override]
    protected function writeContent(Writer $writer): void
    {
    }

    #[Override]
    protected function readContent(Reader $reader): void
    {
    }

    #[Override]
    protected static function readContentRaw(Reader $reader, TagOptions $options): string
    {
        return "";
    }

    #[Override]
    public static function canBeNamed(): bool
    {
        return false;
    }

    #[Override]
    protected function getValueString(): string
    {
        return "";
    }

    #[Override]
    public function jsonSerialize(): null
    {
        return null;
    }

    #[Override]
    public function equals(Tag $tag): bool
    {
        return $tag instanceof EndTag;
    }

    #[Override]
    public function toSNBT(): string
    {
        return "";
    }
}
