<?php

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\Serializer\NbtSerializer;
use Exception;

class FloatTag extends FloatValueTag
{
    use RawValueTag;

    public const TYPE = TagType::TAG_Float;

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function generatePayload(NbtSerializer $serializer): string
    {
        if(!$this->rawValueValid($serializer->getFormat())) {
            return $this->rawValue;
        }
        return $serializer->encodeFloat($this->value);
    }

    /**
     * @inheritDoc
     */
    public function setValue(float $value): FloatValueTag
    {
        if($value !== $this->value) {
            $this->resetRawValue();
        }
        return parent::setValue($value);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function readPayload(Reader $reader): Tag
    {
        $this->rawValue = $reader->read(4);
        $this->rawValueType = $reader->getFormat();
        $this->value = $reader->getSerializer()->decodeFloat($this->rawValue);
        return $this;
    }
}