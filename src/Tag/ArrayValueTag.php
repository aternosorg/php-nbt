<?php


namespace Aternos\Nbt\Tag;


use ArrayAccess;
use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\Serializer\NbtSerializer;
use Countable;
use Exception;
use Iterator;

abstract class ArrayValueTag extends Tag implements Iterator, ArrayAccess, Countable
{
    protected array $valueArray = [];

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function generatePayload(NbtSerializer $serializer): string
    {
        $count = $this->count();
        if($count > 0x7fffffff) {
            throw new Exception("Array exceeds maximum length of " . 0x7fffffff . " entries");
        }
        return $serializer->encodeLengthPrefix($this->count()) . $this->generateValues($serializer);
    }

    /**
     * @inheritDoc
     */
    protected function readPayload(Reader $reader): Tag
    {
        $length = $reader->getSerializer()->readLengthPrefix($reader)->getValue();
        $this->valueArray = $this->readValues($reader, $length);
        return $this;
    }

    /**
     * @param NbtSerializer $serializer
     * @return string
     */
    abstract protected function generateValues(NbtSerializer $serializer): string;

    /**
     * @param Reader $reader
     * @param int $length
     * @return array
     */
    abstract protected function readValues(Reader $reader, int $length): array;

    /**
     * @param $value
     * @return bool
     */
    abstract protected function checkArrayValue($value): bool;

    /**
     * @inheritDoc
     */
    public function current()
    {
        return current($this->valueArray);
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        next($this->valueArray);
    }

    /**
     * @inheritDoc
     */
    public function key()
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
    public function rewind()
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
    public function offsetGet($offset)
    {
        return $this->valueArray[$offset];
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function offsetSet($offset, $value)
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

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
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
     * @param $offset
     * @return bool
     */
    abstract protected function checkArrayKey($offset): bool;

    /**
     * @inheritDoc
     */
    protected function getValueString(): string
    {
        return $this->count() . " entr" . ($this->count() === 1 ? "y" : "ies") . "\n[\n" .
            $this->indent(implode(", \n", array_map("strval", $this->valueArray))) .
            "\n]";
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->valueArray;
    }
}