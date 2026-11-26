<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Exception;
use Override;

class ByteArrayTag extends ArrayValueTag
{
    public const TYPE = TagType::TAG_Byte_Array;

    #[Override]
    protected function writeValues(Writer $writer): string
    {
        foreach ($this->valueArray as $val) {
            $writer->getSerializer()->writeByte($val);
        }
        return "";
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function readValues(Reader $reader, int $length): array
    {
        $values = [];
        for ($i = 0; $i < $length; $i++) {
            $values[] = $reader->getDeserializer()->readByte()->getValue();
        }
        return $values;
    }

    #[Override]
    protected static function readValuesRaw(Reader $reader, int $length): string
    {
        $result = "";
        for ($i = 0; $i < $length; $i++) {
            $result .= $reader->getDeserializer()->readByte()->getRawData();
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
    protected function getValueString(): string
    {
        $values = array_map(
            fn(int $byte): string => sprintf("%02x", $byte & 0xff),
            array_slice($this->valueArray, 0, 32)
        );
        if (count($this->valueArray) > 32) {
            $values[] = "...";
        }
        return $this->count() . " byte" . ($this->count() === 1 ? "" : "s") . " [" . implode(" ", $values) . "]";
    }

    #[Override]
    public function toSNBT(): string
    {
        $values = [];
        foreach ($this->valueArray as $val) {
            $values[] = $val . "b";
        }
        return "[B;" . implode(", ", $values) . "]";
    }
}
