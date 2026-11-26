<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Override;

class IntArrayTag extends ArrayValueTag
{
    public const TYPE = TagType::TAG_Int_Array;

    #[Override]
    protected function writeValues(Writer $writer): string
    {
        foreach ($this->valueArray as $value) {
            $writer->getSerializer()->writeInt($value);
        }
        return "";
    }

    #[Override]
    protected function readValues(Reader $reader, int $length): array
    {
        $values = [];
        for ($i = 0; $i < $length; $i++) {
            $values[] = $reader->getDeserializer()->readInt()->getValue();
        }
        return $values;
    }

    #[Override]
    protected static function readValuesRaw(Reader $reader, int $length): string
    {
        $result = "";
        for ($i = 0; $i < $length; $i++) {
            $result .= $reader->getDeserializer()->readInt()->getRawData();
        }
        return $result;
    }

    #[Override]
    protected function checkArrayValue($value): bool
    {
        return is_int($value);
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
            $values[] = $val;
        }
        return "[I;" . implode(", ", $values) . "]";
    }
}
