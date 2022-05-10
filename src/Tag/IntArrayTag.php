<?php

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;

class IntArrayTag extends ArrayValueTag
{
    public const TYPE = TagType::TAG_Int_Array;

    /**
     * @inheritDoc
     */
    protected function writeValues(Writer $writer): string
    {
        foreach ($this->valueArray as $value) {
            $writer->getSerializer()->writeInt($value);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function readValues(Reader $reader, int $length): array
    {
        $values = [];
        for ($i = 0; $i < $length; $i++) {
            $values[] = $reader->getDeserializer()->readInt()->getValue();
        }
        return $values;
    }

    /**
     * @inheritDoc
     */
    protected static function readValuesRaw(Reader $reader, int $length): string
    {
        $result = "";
        for ($i = 0; $i < $length; $i++) {
            $result .= $reader->getDeserializer()->readInt()->getRawData();
        }
        return $result;
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
