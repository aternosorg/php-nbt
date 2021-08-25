<?php

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\Serializer\NbtSerializer;

class LongArrayTag extends ArrayValueTag
{
    use RawValueTag;

    public const TYPE = TagType::TAG_Long_Array;

    /**
     * @inheritDoc
     */
    protected function generateValues(NbtSerializer $serializer): string
    {
        if($this->rawValueValid($serializer->getFormat())) {
            return $this->rawValue;
        }
        $res = "";
        foreach ($this->valueArray as $value) {
            $res .= $serializer->encodeLong($value);
        }
        return $res;
    }

    /**
     * @inheritDoc
     */
    protected function readValues(Reader $reader, int $length): array
    {
        $raw = "";
        $values = [];
        for($i = 0;$i < $length; $i++) {
            $res = $reader->getSerializer()->readLong($reader);
            $values[] = $res->getValue();
            $raw .= $res->getRawData();
        }
        $this->rawValue = $raw;
        $this->rawValueType = $reader->getSerializer()->getFormat();
        return $values;
    }

    /**
     * @inheritDoc
     */
    protected function checkArrayValue($value): bool
    {
        return is_int($value);
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value): void
    {
        $this->resetRawValue();
        parent::offsetSet($offset, $value);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset): void
    {
        $this->resetRawValue();
        parent::offsetUnset($offset);
    }

    /**
     * @inheritDoc
     */
    protected function checkArrayKey($offset): bool
    {
        return is_int($offset);
    }
}