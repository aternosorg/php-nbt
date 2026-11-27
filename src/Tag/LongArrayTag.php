<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\Deserializer\DeserializerIntReadResult;
use Aternos\Nbt\Deserializer\NbtDeserializer;
use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Aternos\Nbt\Serializer\NbtSerializer;

class LongArrayTag extends NumberArrayTag
{
    use RawValueTag;

    public const TagType TYPE = TagType::LONG_ARRAY;

    protected static function getEntryTagClass(): string
    {
        return LongTag::class;
    }

    protected static function readNumber(NbtDeserializer $deserializer): DeserializerIntReadResult
    {
        return $deserializer->readLong();
    }

    protected static function writeNumber(NbtSerializer $serializer, int $value): void
    {
        $serializer->writeLong($value);
    }

    /**
     * @inheritDoc
     */
    protected function writeValues(Writer $writer): string
    {
        if ($this->rawValueValid($writer->getFormat())) {
            $writer->write($this->rawValue);
            return "";
        }
        return parent::writeValues($writer);
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

    public function setValues(array $values): static
    {
        parent::setValues($values);
        $this->resetRawValue();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value): void
    {
        parent::offsetSet($offset, $value);
        $this->resetRawValue();
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset): void
    {
        parent::offsetUnset($offset);
        $this->resetRawValue();
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
