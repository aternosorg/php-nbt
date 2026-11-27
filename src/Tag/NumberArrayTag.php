<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\Deserializer\DeserializerIntReadResult;
use Aternos\Nbt\Deserializer\NbtDeserializer;
use Aternos\Nbt\Exception\InvalidTagValueException;
use Aternos\Nbt\Exception\NbtException;
use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Aternos\Nbt\Serializer\NbtSerializer;

/**
 * Base class for TAG_Byte_Array, TAG_Int_Array and TAG_Long_Array
 *
 * @extends ArrayValueTag<int>
 */
abstract class NumberArrayTag extends ArrayValueTag
{
    /**
     * @return class-string<IntValueTag> the tag class of a single entry, used for range checks
     */
    abstract protected static function getEntryTagClass(): string;

    /**
     * @throws NbtException
     */
    abstract protected static function readNumber(NbtDeserializer $deserializer): DeserializerIntReadResult;

    /**
     * @throws NbtException
     */
    abstract protected static function writeNumber(NbtSerializer $serializer, int $value): void;

    /**
     * @return list<int>
     */
    public function getValues(): array
    {
        return $this->valueArray;
    }

    /**
     * Replace all values
     *
     * @param list<int> $values keys are ignored, the values are stored with continuous indices starting at 0
     * @return $this
     * @throws InvalidTagValueException if a value is not an int or out of range for the entry type
     */
    public function setValues(array $values): static
    {
        foreach ($values as $value) {
            $this->checkValue($value);
        }
        $this->checkLength(count($values));
        $this->valueArray = array_values($values);
        return $this;
    }

    protected function checkValue(mixed $value): void
    {
        if (!is_int($value)) {
            throw new InvalidTagValueException("Invalid value of type " . get_debug_type($value) . " for " . $this->getType()->getName());
        }
        static::getEntryTagClass()::checkValueRange($value, $this->getType());
    }

    protected function writeValues(Writer $writer): string
    {
        $serializer = $writer->getSerializer();
        foreach ($this->valueArray as $value) {
            static::writeNumber($serializer, $value);
        }
        return "";
    }

    protected function readValues(Reader $reader, int $length): array
    {
        $deserializer = $reader->getDeserializer();
        $values = [];
        for ($i = 0; $i < $length; $i++) {
            $values[] = static::readNumber($deserializer)->getValue();
        }
        return $values;
    }

    protected static function readValuesRaw(Reader $reader, int $length): string
    {
        $deserializer = $reader->getDeserializer();
        $result = "";
        for ($i = 0; $i < $length; $i++) {
            $result .= static::readNumber($deserializer)->getRawData();
        }
        return $result;
    }

    public function equals(Tag $tag): bool
    {
        return $tag instanceof NumberArrayTag && $this->getType() === $tag->getType() && $tag->valueArray === $this->valueArray;
    }
}
