<?php

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\Serializer\NbtSerializer;

class IntArrayTag extends ArrayValueTag
{
    public const TYPE = TagType::TAG_Int_Array;

    /**
     * @inheritDoc
     */
    protected function generateValues(NbtSerializer $serializer): string
    {
        $res = "";
        foreach ($this->valueArray as $value) {
            $res .= $serializer->encodeInt($value);
        }
        return $res;
    }

    /**
     * @inheritDoc
     */
    protected function readValues(Reader $reader, int $length): array
    {
        $values = [];
        for($i = 0;$i < $length; $i++) {
            $values[] = $reader->getSerializer()->readInt($reader)->getValue();
        }
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
    protected function checkArrayKey($offset): bool
    {
        return is_int($offset);
    }
}