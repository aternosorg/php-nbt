<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use ArrayAccess;
use Aternos\Nbt\Exception\InvalidTagValueException;
use Aternos\Nbt\Exception\NbtException;
use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use BadMethodCallException;
use Countable;
use Iterator;

/**
 * @template TValue of Tag|int
 * @implements Iterator<int, TValue>
 * @implements ArrayAccess<int, TValue>
 */
abstract class ArrayValueTag extends Tag implements Iterator, ArrayAccess, Countable
{
    /**
     * @var array<int, TValue>
     */
    protected array $valueArray = [];

    /**
     * @inheritDoc
     * @throws NbtException
     */
    public function writeContent(Writer $writer): static
    {
        $count = $this->count();
        if ($count > 0x7fffffff) {
            throw new InvalidTagValueException("Array exceeds maximum length of " . 0x7fffffff . " entries");
        }
        $writer->getSerializer()->writeLengthPrefix($count);
        $this->writeValues($writer);
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function readContent(Reader $reader): static
    {
        $length = $reader->getDeserializer()->readLengthPrefix()->getValue();
        $this->valueArray = $this->readValues($reader, $length);
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected static function readContentRaw(Reader $reader, TagOptions $options): string
    {
        $length = $reader->getDeserializer()->readLengthPrefix();
        $valueData = static::readValuesRaw($reader, $length->getValue());
        return $length->getRawData() . $valueData;
    }

    /**
     * Write all values without the length prefix
     *
     * @param Writer $writer
     * @return string not used by callers, implementations return an empty string
     */
    abstract protected function writeValues(Writer $writer): string;

    /**
     * @param Reader $reader
     * @param int $length
     * @return list<TValue>
     */
    abstract protected function readValues(Reader $reader, int $length): array;

    /**
     * @param Reader $reader
     * @param int $length
     * @return string
     */
    protected static function readValuesRaw(Reader $reader, int $length): string
    {
        throw new BadMethodCallException("Not implemented");
    }

    /**
     * @param mixed $value
     * @return bool
     */
    abstract protected function checkArrayValue($value): bool;

    /**
     * @inheritDoc
     * @return TValue|false
     */
    public function current(): mixed
    {
        return current($this->valueArray);
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        next($this->valueArray);
    }

    /**
     * @inheritDoc
     */
    public function key(): string|int|null
    {
        return key($this->valueArray);
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return current($this->valueArray) !== false;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        reset($this->valueArray);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->valueArray);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset): mixed
    {
        return $this->valueArray[$offset];
    }

    /**
     * @inheritDoc
     * @throws InvalidTagValueException
     */
    public function offsetSet($offset, $value): void
    {
        if (!$this->checkArrayValue($value)) {
            throw new InvalidTagValueException("Invalid array value");
        }
        if (is_null($offset)) {
            $this->valueArray[] = $value;
            return;
        }
        if (!$this->checkArrayKey($offset)) {
            throw new InvalidTagValueException("Invalid array offset " . $offset);
        }
        $this->valueArray[$offset] = $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset): void
    {
        unset($this->valueArray[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->valueArray);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    abstract protected function checkArrayKey($offset): bool;

    /**
     * @inheritDoc
     */
    protected function getValueString(): string
    {
        return $this->count() . " entr" . ($this->count() === 1 ? "y" : "ies") . "\n[\n" .
            $this->indent(implode(", \n", array_map(strval(...), $this->valueArray))) .
            "\n]";
    }

    /**
     * @inheritDoc
     * @return array<int, TValue>
     */
    public function jsonSerialize(): array
    {
        return $this->valueArray;
    }

    /**
     * @inheritDoc
     */
    public function equals(Tag $tag): bool
    {
        if ($tag === $this) {
            return true;
        }
        if (!$tag instanceof ArrayValueTag || $this->getType() !== $tag->getType() || count($tag) !== count($this)) {
            return false;
        }
        foreach ($this as $i => $val) {
            if ($val !== $tag[$i]) {
                return false;
            }
        }
        return true;
    }
}
