<?php

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Exception;

class ByteArrayTag extends ArrayValueTag
{
    public const TYPE = TagType::TAG_Byte_Array;

    /**
     * @inheritDoc
     */
    protected function writeValues(Writer $writer): string
    {
        foreach ($this->valueArray as $val) {
            $writer->getSerializer()->writeByte($val);
        }
        return $this;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function readValues(Reader $reader, int $length): array
    {
        $values = [];
        for ($i = 0; $i < $length; $i++) {
            $values[] = $reader->getDeserializer()->readByte()->getValue();
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
            $result .= $reader->getDeserializer()->readByte()->getRawData();
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

    /**
     * @inheritDoc
     */
    protected function getValueString(): string
    {
        $values = array_map(function ($elem) {
            return str_pad(dechex($elem), 2, "0");
        }, array_slice($this->valueArray, 0, 32));
        if (count($this->valueArray) > 32) {
            $values[] = "...";
        }
        return $this->count() . " byte" . ($this->count() === 1 ? "" : "s") . " [" . implode(" ", $values) . "]";
    }

    /**
     * @inheritDoc
     */
    public function toSNBT(): string
    {
        $values = [];
        foreach ($this->valueArray as $val) {
            $values[] = $val . "b";
        }
        return "[B;" . implode(", ", $values) . "]";
    }
}
