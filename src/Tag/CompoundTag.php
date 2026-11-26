<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use ArrayAccess;
use Aternos\Nbt\Exception\InvalidTagValueException;
use Aternos\Nbt\Exception\NbtException;
use Aternos\Nbt\Exception\RawTagModificationException;
use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Aternos\Nbt\NbtFormat;
use Countable;
use Generator;
use IteratorAggregate;
use Override;

/**
 * A map of named tags that keeps the insertion order
 *
 * @implements IteratorAggregate<string, Tag>
 * @implements ArrayAccess<string, Tag>
 */
class CompoundTag extends Tag implements IteratorAggregate, ArrayAccess, Countable
{
    public const TagType TYPE = TagType::COMPOUND;

    /**
     * Children are looked up by their current name, so renaming a child with setName() also changes its key
     *
     * @var list<Tag>
     */
    protected array $valueArray = [];
    protected ?string $rawContent = null;
    protected ?NbtFormat $rawContentFormat = null;

    #[Override]
    public function getType(): TagType
    {
        return self::TYPE;
    }

    public function isRaw(): bool
    {
        return $this->rawContent !== null;
    }

    /**
     * @throws RawTagModificationException
     */
    protected function assertNotRaw(): void
    {
        if ($this->isRaw()) {
            throw new RawTagModificationException("Raw " . $this->describe() . " cannot be modified");
        }
    }

    protected function describe(): string
    {
        return $this->getType()->getName() . " '" . ($this->getStringPath() ?? $this->name) . "'";
    }

    #[Override]
    protected function writeContent(Writer $writer): void
    {
        if ($this->rawContent !== null) {
            if ($this->rawContentFormat !== $writer->getFormat()) {
                throw new RawTagModificationException("Cannot change format of raw " . $this->describe());
            }
            $writer->write($this->rawContent);
            return;
        }

        $writtenNames = [];
        foreach ($this->valueArray as $value) {
            $name = $value->getName() ?? "";
            if (isset($writtenNames[$name])) {
                throw new InvalidTagValueException("Duplicate key '" . $name . "' in " . $this->describe());
            }
            $writtenNames[$name] = true;
            $value->writeData($writer);
        }
        new EndTag($this->options)->writeData($writer);
    }

    #[Override]
    protected function readContent(Reader $reader): void
    {
        if ($this->options->shouldBeReadRaw($this)) {
            $this->rawContentFormat = $reader->getFormat();
            $this->rawContent = static::readContentRaw($reader, $this->options);
            return;
        }
        while (!($tag = Tag::load($reader, $this->options, $this)) instanceof EndTag) {
            $this->valueArray[] = $tag;
        }
    }

    #[Override]
    protected static function readContentRaw(Reader $reader, TagOptions $options): string
    {
        $result = "";
        do {
            $tag = Tag::loadRaw($reader, $options);
            $result .= $tag->data;
        } while ($tag->tagType !== TagType::END);
        return $result;
    }

    /**
     * @param string|null $offset the name of the tag, null to use the existing name of $value
     * @param Tag $value
     * @throws NbtException
     */
    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!$value instanceof Tag || $value instanceof EndTag) {
            throw new InvalidTagValueException("Invalid value of type " . get_debug_type($value) . " for " . $this->describe());
        }
        if (!is_string($offset) && $offset !== null) {
            throw new InvalidTagValueException("Invalid key of type " . get_debug_type($offset) . " for " . $this->describe() . ", keys must be strings");
        }
        $this->assertNotRaw();

        $name = $offset ?? $value->getName()
            ?? throw new InvalidTagValueException("Tags inside a CompoundTag must be named, " . $value->getType()->getName() . " has no name");
        // $value may already be a child under another name, e.g. $compound["new"] = $compound["old"]
        $this->removeChild($value);
        $this->offsetUnset($name);
        $this->valueArray[] = $value->setName($name)->setParentTag($this);
    }

    /**
     * Iterates over the children with their names as keys (also numeric names stay strings)
     *
     * @return Generator<string, Tag>
     */
    #[Override]
    public function getIterator(): Generator
    {
        foreach ($this->valueArray as $value) {
            yield $value->getName() ?? "" => $value;
        }
    }

    #[Override]
    public function offsetExists(mixed $offset): bool
    {
        return $this->offsetGet($offset) !== null;
    }

    #[Override]
    public function offsetGet(mixed $offset): ?Tag
    {
        return array_find($this->valueArray, fn(Tag $value): bool => $value->getName() === $offset);
    }

    /**
     * @throws RawTagModificationException
     */
    #[Override]
    public function offsetUnset(mixed $offset): void
    {
        $this->assertNotRaw();
        $child = $this->offsetGet($offset);
        if ($child !== null) {
            $this->removeChild($child);
        }
    }

    /**
     * Remove $child if it is a child of this tag, compared by identity instead of by name
     */
    protected function removeChild(Tag $child): void
    {
        $index = array_find_key($this->valueArray, fn(Tag $value): bool => $value === $child);
        if ($index === null) {
            return;
        }
        $child->setParentTag(null);
        array_splice($this->valueArray, $index, 1);
    }

    #[Override]
    public function count(): int
    {
        return count($this->valueArray);
    }

    #[Override]
    protected function getValueString(): string
    {
        if ($this->rawContent !== null) {
            return strlen($this->rawContent) . " bytes";
        }
        $count = $this->count();
        return $count . " entr" . ($count === 1 ? "y" : "ies") . "\n{\n" .
            $this->indent(implode(", \n", array_map(strval(...), $this->valueArray))) .
            "\n}";
    }

    /**
     * @return array<string, Tag>
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return iterator_to_array($this->getIterator());
    }

    /**
     * Set a child tag by name
     *
     * @param string|null $name the name of the tag, null to use the existing name of $tag
     * @throws NbtException
     */
    public function set(?string $name, Tag $tag): static
    {
        $this->offsetSet($name, $tag);
        return $this;
    }

    /**
     * @throws RawTagModificationException
     */
    public function delete(string $name): static
    {
        $this->offsetUnset($name);
        return $this;
    }

    public function get(string $name): ?Tag
    {
        return $this->offsetGet($name);
    }

    /**
     * @template T of Tag
     * @param class-string<T> $class
     * @return T|null
     */
    public function getOfClass(string $name, string $class): ?Tag
    {
        $tag = $this->get($name);
        return $tag instanceof $class ? $tag : null;
    }

    public function getByte(string $name): ?ByteTag
    {
        return $this->getOfClass($name, ByteTag::class);
    }

    public function getShort(string $name): ?ShortTag
    {
        return $this->getOfClass($name, ShortTag::class);
    }

    public function getInt(string $name): ?IntTag
    {
        return $this->getOfClass($name, IntTag::class);
    }

    public function getLong(string $name): ?LongTag
    {
        return $this->getOfClass($name, LongTag::class);
    }

    public function getFloat(string $name): ?FloatTag
    {
        return $this->getOfClass($name, FloatTag::class);
    }

    public function getDouble(string $name): ?DoubleTag
    {
        return $this->getOfClass($name, DoubleTag::class);
    }

    public function getString(string $name): ?StringTag
    {
        return $this->getOfClass($name, StringTag::class);
    }

    public function getByteArray(string $name): ?ByteArrayTag
    {
        return $this->getOfClass($name, ByteArrayTag::class);
    }

    public function getIntArray(string $name): ?IntArrayTag
    {
        return $this->getOfClass($name, IntArrayTag::class);
    }

    public function getLongArray(string $name): ?LongArrayTag
    {
        return $this->getOfClass($name, LongArrayTag::class);
    }

    public function getCompound(string $name): ?CompoundTag
    {
        return $this->getOfClass($name, CompoundTag::class);
    }

    /**
     * @param TagType|null $contentType required content type of the list, null to accept any type
     */
    public function getList(string $name, ?TagType $contentType = null): ?ListTag
    {
        $list = $this->getOfClass($name, ListTag::class);
        if ($list === null || ($contentType !== null && $list->getContentType() !== $contentType)) {
            return null;
        }
        return $list;
    }

    #[Override]
    public function equals(Tag $tag): bool
    {
        if ($tag === $this) {
            return true;
        }
        if (!$tag instanceof CompoundTag || count($tag) !== count($this)) {
            return false;
        }
        foreach ($this->valueArray as $value) {
            $other = $tag->offsetGet($value->getName() ?? "");
            if ($other === null || !$value->equals($other)) {
                return false;
            }
        }
        return true;
    }

    #[Override]
    public function toSNBT(): string
    {
        $entries = array_map(
            fn(Tag $value): string => StringTag::encodeSNBTString($value->getName() ?? "") . ": " . $value->toSNBT(),
            $this->valueArray
        );
        return "{" . implode(", ", $entries) . "}";
    }
}
