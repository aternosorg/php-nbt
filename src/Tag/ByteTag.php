<?php


namespace Aternos\Nbt\Tag;


use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\Serializer\NbtSerializer;
use Exception;

class ByteTag extends IntValueTag
{
    public const TYPE = TagType::TAG_Byte;

    /**
     * @inheritDoc
     */
    public function generatePayload(NbtSerializer $serializer): string
    {
        return $serializer->encodeByte($this->value);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function readPayload(Reader $reader): Tag
    {
        $this->value = $reader->getSerializer()->decodeByte($reader->read(1));
        return $this;
    }
}