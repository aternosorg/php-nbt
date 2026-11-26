<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use ArrayAccess;
use ArrayIterator;
use Aternos\Nbt\Exception\InvalidTagValueException;
use Aternos\Nbt\Exception\NbtException;
use Countable;
use IteratorAggregate;
use Override;

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
     * @phpstan-assert-if-true TValue $value
     */
    abstract protected function isValidValue(mixed $value): bool;

    /**
     * @throws InvalidTagValueException
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
    #[Override]
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->valueArray);
    }

    #[Override]
    public function offsetExists(mixed $offset): bool
    {
        return is_int($offset) && array_key_exists($offset, $this->valueArray);
    }

    /**
     * @return TValue|null
     */
    #[Override]
    public function offsetGet(mixed $offset): mixed
    {
        if (!is_int($offset)) {
            return null;
        }
        return $this->valueArray[$offset] ?? null;
    }

    /**
     * @param int|null $offset an existing index to replace a value, the current length or null to append
     * @throws NbtException
     */
    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!$this->isValidValue($value)) {
            throw new InvalidTagValueException("Invalid value of type " . get_debug_type($value) . " for " . $this->getType()->getName());
        }
        $count = count($this->valueArray);
        if ($offset === null || $offset === $count) {
            $this->checkLength($count + 1);
            $this->valueArray[] = $value;
            return;
        }
        if (!is_int($offset) || $offset < 0 || $offset >= $count) {
            throw new InvalidTagValueException("Invalid offset " . var_export($offset, true) . " for " . $this->getType()->getName() .
                " with " . $count . " entries");
        }
        array_splice($this->valueArray, $offset, 1, [$value]);
    }

    /**
     * @throws NbtException
     */
    #[Override]
    public function offsetUnset(mixed $offset): void
    {
        if (!is_int($offset) || !array_key_exists($offset, $this->valueArray)) {
            return;
        }
        array_splice($this->valueArray, $offset, 1);
    }

    #[Override]
    public function count(): int
    {
        return count($this->valueArray);
    }

    /**
     * @return list<string> human-readable representation of each entry
     */
    abstract protected function getEntryStrings(): array;

    #[Override]
    protected function getValueString(): string
    {
        $count = $this->count();
        return $count . " entr" . ($count === 1 ? "y" : "ies") . "\n[\n" .
            $this->indent(implode(", \n", $this->getEntryStrings())) .
            "\n]";
    }

    /**
     * @return list<TValue>
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return $this->valueArray;
    }
}
