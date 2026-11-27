<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use ArrayAccess;
use ArrayIterator;
use Aternos\Nbt\Exception\InvalidTagValueException;
use Aternos\Nbt\Exception\NbtException;
use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use BadMethodCallException;
use Countable;
use IteratorAggregate;

/**
 * Base class for tags that hold a list of values (TAG_List and the number array tags)
 *
 * Values are always stored as a list: unsetting an entry moves all following entries one index down.
 *
 * @template TValue
 * @implements IteratorAggregate<int, TValue>
 * @implements ArrayAccess<int, TValue>
 */
abstract class ArrayValueTag extends Tag implements IteratorAggregate, ArrayAccess, Countable
{
    /**
     * Maximum number of entries, limited by the signed 32-bit length prefix
     */
    public const int MAX_LENGTH = 0x7fffffff;

    /**
     * @var list<TValue>
     */
    protected array $valueArray = [];

    /**
     * @inheritDoc
     * @throws NbtException
     */
    public function writeContent(Writer $writer): static
    {
        $count = $this->count();
        $this->checkLength($count);
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
     * Read $length values that follow the length prefix
     *
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
     * Throw if $value cannot be stored in this tag
     *
     * @throws InvalidTagValueException
     */
    abstract protected function checkValue(mixed $value): void;

    /**
     * @throws InvalidTagValueException if $length exceeds MAX_LENGTH
     */
    protected function checkLength(int $length): void
    {
        if ($length > static::MAX_LENGTH) {
            throw new InvalidTagValueException($this->getType()->getName() . " exceeds maximum length of " . static::MAX_LENGTH . " entries");
        }
    }

    /**
     * @return ArrayIterator<int, TValue>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->valueArray);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return is_int($offset) && array_key_exists($offset, $this->valueArray);
    }

    /**
     * @return TValue|null null if there is no entry at $offset
     */
    public function offsetGet($offset): mixed
    {
        if (!is_int($offset)) {
            return null;
        }
        return $this->valueArray[$offset] ?? null;
    }

    /**
     * @param int|null $offset an existing index to replace a value, the current length or null to append
     * @throws InvalidTagValueException if $value is invalid or $offset is neither an existing index nor the end of the list
     */
    public function offsetSet($offset, $value): void
    {
        $this->checkValue($value);
        $count = count($this->valueArray);
        if ($offset === null || $offset === $count) {
            $this->checkLength($count + 1);
            $this->valueArray[] = $value;
            return;
        }
        if (!is_int($offset) || $offset < 0 || $offset >= $count) {
            throw new InvalidTagValueException("Invalid offset " . var_export($offset, true) . " for " .
                $this->getType()->getName() . " with " . $count . " entries");
        }
        $this->valueArray[$offset] = $value;
    }

    /**
     * Remove the entry at $offset and move all following entries one index down
     */
    public function offsetUnset($offset): void
    {
        if (!$this->offsetExists($offset)) {
            return;
        }
        array_splice($this->valueArray, $offset, 1);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->valueArray);
    }

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
     * @return list<TValue>
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
