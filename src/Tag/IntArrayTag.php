<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\Exception\InvalidTagValueException;
use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;

class IntArrayTag extends ArrayValueTag
{
    public const TagType TYPE = TagType::INT_ARRAY;

    /**
     * @inheritDoc
     */
    protected function writeValues(Writer $writer): string
    {
        foreach ($this->valueArray as $value) {
            $writer->getSerializer()->writeInt($value);
        }
        return "";
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
     * @throws InvalidTagValueException if $value is an int outside the range of IntTag
     */
    public function offsetSet($offset, $value): void
    {
        if (is_int($value)) {
            IntTag::checkValueRange($value, $this->getType());
        }
        parent::offsetSet($offset, $value);
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
    public function toSNBT(): string
    {
        $values = [];
        foreach ($this->valueArray as $val) {
            $values[] = $val;
        }
        return "[I;" . implode(", ", $values) . "]";
    }
}
