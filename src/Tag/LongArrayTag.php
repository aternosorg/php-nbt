<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Override;

class LongArrayTag extends ArrayValueTag
{
    use RawValueTag;

    public const TYPE = TagType::TAG_Long_Array;

    #[Override]
    protected function writeValues(Writer $writer): string
    {
        if ($this->rawValueValid($writer->getFormat())) {
            $writer->write($this->rawValue);
            return "";
        }
        foreach ($this->valueArray as $value) {
            $writer->getSerializer()->writeLong($value);
        }
        return "";
    }

    #[Override]
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

    #[Override]
    protected static function readValuesRaw(Reader $reader, int $length): string
    {
        $raw = "";
        for ($i = 0; $i < $length; $i++) {
            $raw .= $reader->getDeserializer()->readLong()->getRawData();
        }
        return $raw;
    }

    #[Override]
    protected function checkArrayValue($value): bool
    {
        return is_int($value);
    }

    #[Override]
    public function offsetSet($offset, $value): void
    {
        $this->resetRawValue();
        parent::offsetSet($offset, $value);
    }

    #[Override]
    public function offsetUnset($offset): void
    {
        $this->resetRawValue();
        parent::offsetUnset($offset);
    }

    #[Override]
    protected function checkArrayKey($offset): bool
    {
        return is_int($offset);
    }

    #[Override]
    public function toSNBT(): string
    {
        $values = [];
        foreach ($this->valueArray as $val) {
            $values[] = $val . "L";
        }
        return "[L;" . implode(", ", $values) . "]";
    }
}
