<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Override;

class EndTag extends Tag
{
    public const TYPE = TagType::TAG_End;

    #[Override]
    public function writeContent(Writer $writer): static
    {
        return $this;
    }

    #[Override]
    protected function readContent(Reader $reader): static
    {
        return $this;
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
    public function jsonSerialize(): mixed
    {
        return null;
    }

    #[Override]
    function equals(Tag $tag): bool
    {
        return $tag->getType() === $this->getType();
    }

    #[Override]
    public function toSNBT(): string
    {
        return "";
    }
}
