<?php

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\Serializer\NbtSerializer;

class EndTag extends Tag
{
    public const TYPE = TagType::TAG_End;

    /**
     * @inheritDoc
     */
    public function generatePayload(NbtSerializer $serializer): string
    {
        return "";
    }

    /**
     * @inheritDoc
     */
    protected function readPayload(Reader $reader): Tag
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function canBeNamed(): bool
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
    public function jsonSerialize()
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
