<?php

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\Serializer\NbtSerializer;

class IntTag extends IntValueTag
{
    public const TYPE = TagType::TAG_Int;

    /**
     * @inheritDoc
     */
    public function generatePayload(NbtSerializer $serializer): string
    {
        return $serializer->encodeInt($this->value);
    }

    /**
     * @inheritDoc
     */
    protected function readPayload(Reader $reader): Tag
    {
        $this->value = $reader->getSerializer()->readInt($reader)->getValue();
        return $this;
    }
}