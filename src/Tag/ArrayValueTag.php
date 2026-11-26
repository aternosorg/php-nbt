<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use ArrayAccess;
use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use BadMethodCallException;
use Countable;
use Exception;
use Iterator;
use Override;

abstract class ArrayValueTag extends Tag implements Iterator, ArrayAccess, Countable
{
    protected array $valueArray = [];

    /**
     * @throws Exception
     */
    #[Override]
    public function writeContent(Writer $writer): static
    {
        $count = $this->count();
        if ($count > 0x7fffffff) {
            throw new Exception("Array exceeds maximum length of " . 0x7fffffff . " entries");
        }
        $writer->getSerializer()->writeLengthPrefix($count);
        $this->writeValues($writer);
        return $this;
    }

    #[Override]
    protected function readContent(Reader $reader): static
    {
        $length = $reader->getDeserializer()->readLengthPrefix()->getValue();
        $this->valueArray = $this->readValues($reader, $length);
        return $this;
    }

    #[Override]
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
     * @return array
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
     * @param $value
     * @return bool
     */
    abstract protected function checkArrayValue($value): bool;

    #[Override]
    public function current(): mixed
    {
        return current($this->valueArray);
    }

    #[Override]
    public function next(): void
    {
        next($this->valueArray);
    }

    #[Override]
    public function key(): string|int|null
    {
        return key($this->valueArray);
    }

    #[Override]
    public function valid(): bool
    {
        return current($this->valueArray) !== false;
    }

    #[Override]
    public function rewind(): void
    {
        reset($this->valueArray);
    }

    #[Override]
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->valueArray);
    }

    #[Override]
    public function offsetGet($offset): mixed
    {
        return $this->valueArray[$offset];
    }

    /**
     * @throws Exception
     */
    #[Override]
    public function offsetSet($offset, $value): void
    {
        if (!$this->checkArrayValue($value)) {
            throw new Exception("Invalid array value");
        }
        if (is_null($offset)) {
            $this->valueArray[] = $value;
            return;
        }
        if (!$this->checkArrayKey($offset)) {
            throw new Exception("Invalid array offset " . $offset);
        }
        $this->valueArray[$offset] = $value;
    }

    #[Override]
    public function offsetUnset($offset): void
    {
        unset($this->valueArray[$offset]);
    }

    #[Override]
    public function count(): int
    {
        return count($this->valueArray);
    }

    /**
     * @param $offset
     * @return bool
     */
    abstract protected function checkArrayKey($offset): bool;

    #[Override]
    protected function getValueString(): string
    {
        return $this->count() . " entr" . ($this->count() === 1 ? "y" : "ies") . "\n[\n" .
            $this->indent(implode(", \n", array_map(strval(...), $this->valueArray))) .
            "\n]";
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return $this->valueArray;
    }

    #[Override]
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
