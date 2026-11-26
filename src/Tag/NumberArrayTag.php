<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\Deserializer\DeserializerIntReadResult;
use Aternos\Nbt\Deserializer\NbtDeserializer;
use Aternos\Nbt\Exception\InvalidTagValueException;
use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Aternos\Nbt\Serializer\NbtSerializer;
use Override;

/**
 * Base class for TAG_Byte_Array, TAG_Int_Array and TAG_Long_Array
 *
 * @extends ArrayValueTag<int>
 */
abstract class NumberArrayTag extends ArrayValueTag
{
    /**
     * @return class-string<IntValueTag> the tag class of a single entry, used for reading, writing and range checks
     */
    abstract protected static function getEntryTagClass(): string;

    abstract protected static function readNumber(NbtDeserializer $deserializer): DeserializerIntReadResult;

    abstract protected static function writeNumber(NbtSerializer $serializer, int $value): void;

    /**
     * @return list<int>
     */
    public function getValues(): array
    {
        return $this->valueArray;
    }

    /**
     * @param list<int> $values keys are ignored, the values are stored with continuous indices starting at 0
     * @throws InvalidTagValueException if a value is not an int or out of range for the entry type
     */
    public function setValues(array $values): static
    {
        foreach ($values as $value) {
            if (!is_int($value)) {
                throw new InvalidTagValueException("Invalid value of type " . get_debug_type($value) . " for " . $this->getType()->getName());
            }
            if (!$this->isValidValue($value)) {
                throw new InvalidTagValueException("Value " . $value . " is out of range for " . $this->getType()->getName());
            }
        }
        $this->checkLength(count($values));
        $this->valueArray = array_values($values);
        return $this;
    }

    #[Override]
    protected function getEntryStrings(): array
    {
        return array_map(strval(...), $this->valueArray);
    }

    #[Override]
    protected function isValidValue(mixed $value): bool
    {
        return is_int($value) && static::getEntryTagClass()::isValidValue($value);
    }

    #[Override]
    protected function writeContent(Writer $writer): void
    {
        $serializer = $writer->getSerializer();
        $serializer->writeLengthPrefix($this->count());
        foreach ($this->valueArray as $value) {
            static::writeNumber($serializer, $value);
        }
    }

    #[Override]
    protected function readContent(Reader $reader): void
    {
        $deserializer = $reader->getDeserializer();
        $length = $deserializer->readLengthPrefix()->value;
        $values = [];
        for ($i = 0; $i < $length; $i++) {
            $values[] = static::readNumber($deserializer)->value;
        }
        $this->valueArray = $values;
    }

    #[Override]
    protected static function readContentRaw(Reader $reader, TagOptions $options): string
    {
        $deserializer = $reader->getDeserializer();
        $length = $deserializer->readLengthPrefix();
        $result = $length->rawData;
        for ($i = 0; $i < $length->value; $i++) {
            $result .= static::readNumber($deserializer)->rawData;
        }
        return $result;
    }

    #[Override]
    public function equals(Tag $tag): bool
    {
        return $tag instanceof NumberArrayTag && $this->getType() === $tag->getType() && $tag->valueArray === $this->valueArray;
    }
}
