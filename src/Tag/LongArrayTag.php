<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;

/**
 * @extends ArrayValueTag<int>
 */
class LongArrayTag extends ArrayValueTag
{
    public const TagType TYPE = TagType::LONG_ARRAY;

    /**
     * @inheritDoc
     */
    protected function writeValues(Writer $writer): string
    {
        foreach ($this->valueArray as $value) {
            $writer->getSerializer()->writeLong($value);
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
            $values[] = $reader->getDeserializer()->readLong()->getValue();
        }
        return $values;
    }

    /**
     * @inheritDoc
     */
    protected static function readValuesRaw(Reader $reader, int $length): string
    {
        $raw = "";
        for ($i = 0; $i < $length; $i++) {
            $raw .= $reader->getDeserializer()->readLong()->getRawData();
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
            $values[] = $val . "L";
        }
        return "[L;" . implode(", ", $values) . "]";
    }
}
