<?php

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;

class EndTag extends Tag
{
    public const TYPE = TagType::TAG_End;

    /**
     * @inheritDoc
     */
    public function writeContent(Writer $writer): static
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function readContent(Reader $reader): static
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected static function readContentRaw(Reader $reader, TagOptions $options): string
    {
        return "";
    }

    /**
     * @inheritDoc
     */
    public static function canBeNamed(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    protected function getValueString(): string
    {
        return "";
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    function equals(Tag $tag): bool
    {
        return $tag->getType() === $this->getType();
    }
}
