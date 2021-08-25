<?php

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\Serializer\NbtSerializer;
use Exception;

class ShortTag extends IntValueTag
{
    public const TYPE = TagType::TAG_Short;

    /**
     * @inheritDoc
     */
    public function generatePayload(NbtSerializer $serializer): string
    {
        return $serializer->encodeShort($this->value);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function readPayload(Reader $reader): Tag
    {
        $this->value = $reader->getSerializer()->decodeShort($reader->read(2));
        return $this;
    }
}