<?php

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;

class LongArrayTag extends ArrayValueTag
{
    use RawValueTag;

    public const TYPE = TagType::TAG_Long_Array;

    /**
     * @inheritDoc
     */
    protected function writeValues(Writer $writer): string
    {
        if ($this->rawValueValid($writer->getFormat())) {
            $writer->write($this->rawValue);
            return $this;
        }
        foreach ($this->valueArray as $value) {
            $writer->getSerializer()->writeLong($value);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function readValues(Reader $reader, int $length): array
    {
        $raw = "";
        $values = [];
        for ($i = 0; $i < $length; $i++) {
            $res = $reader->getDeserializer()->readLong();
            $values[] = $res->getValue();
            $raw .= $res->getRawData();
        }
        $this->rawValue = $raw;
        $this->rawValueType = $reader->getFormat();
        return $values;
    }

    /**
     * @inheritDoc
     */
    protected static function readValuesRaw(Reader $reader, int $length): string
    {
        $raw = "";
        for ($i = 0; $i < $length; $i++) {
            $raw = $reader->getDeserializer()->readLong()->getRawData();
        }
        return $raw;
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
