<?php

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\Serializer\NbtSerializer;
use Exception;

class ByteArrayTag extends ArrayValueTag
{
    public const TYPE = TagType::TAG_Byte_Array;

    /**
     * @inheritDoc
     */
    protected function generateValues(NbtSerializer $serializer): string
    {
        $res = "";
        foreach ($this->valueArray as $val) {
            $res .= $serializer->encodeByte($val);
        }
        return $res;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function readValues(Reader $reader, int $length): array
    {
        $values = [];
        for ($i = 0; $i < $length; $i++) {
            $values[] = $reader->getSerializer()->decodeByte($reader->read(1));
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

    /**
     * @inheritDoc
     */
    protected function getValueString(): string
    {
        $values = array_map(function ($elem) {
            return str_pad(dechex($elem), 2, "0", STR_PAD_RIGHT);
        }, array_slice($this->valueArray, 0, 32));
        if(count($this->valueArray) > 32) {
            $values[] = "...";
        }
        return $this->count() . " byte" . ($this->count() === 1 ? "" : "s") . " [" . implode(" ", $values) . "]";
    }
}
