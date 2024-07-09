<?php

namespace Aternos\Nbt\Tag;

use ArrayAccess;
use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Countable;
use Exception;
use Iterator;

class CompoundTag extends Tag implements Iterator, ArrayAccess, Countable
{
    public const TYPE = TagType::TAG_Compound;

    /**
     * @var Tag[]
     */
    protected array $valueArray = [];
    protected ?string $rawContent = null;
    protected ?int $rawContentFormat = null;

    /**
     * @return bool
     */
    public function isRaw(): bool
    {
        return $this->rawContent !== null;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function writeContent(Writer $writer): static
    {
        if($this->isRaw()) {
            if($this->rawContentFormat !== $writer->getFormat()) {
                throw new Exception("Cannot change format of raw compound tag");
            }
            $writer->write($this->rawContent);
            return $this;
        }

        $writtenNames = [];
        foreach ($this->valueArray as $value) {
            if (in_array($value->getName(), $writtenNames)) {
                throw new Exception("Duplicate key '" . $value->getName() . "' in compound tag");
            }
            $value->writeData($writer);
        }
        (new EndTag($this->options))->writeData($writer);
        return $this;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function readContent(Reader $reader): static
    {
        if($this->options->shouldBeReadRaw($this)) {
            $this->rawContentFormat = $reader->getFormat();
            $this->rawContent = static::readContentRaw($reader, $this->options);
            return $this;
        }
        while (!(($tag = Tag::load($reader, $this->options, $this)) instanceof EndTag)) {
            $this->valueArray[] = $tag->setParentTag($this);
        }
        return $this;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected static function readContentRaw(Reader $reader, TagOptions $options): string
    {
        $result = "";
        do {
            $tag = Tag::loadRaw($reader, $options);
            $result .= $tag->getData();
        } while ($tag->getTagType() !== TagType::TAG_End);
        return $result;
    }

    /**
     * @param Tag $value
     * @inheritDoc
     * @throws Exception
     */
    public function offsetSet($offset, $value): void
    {
        if (!($value instanceof Tag) || $value instanceof EndTag) {
            throw new Exception("Invalid CompoundTag value");
        }
        if (!is_string($offset) && !is_null($offset)) {
            throw new Exception("Invalid CompoundTag key");
        }
        if($this->isRaw()) {
            throw new Exception("Raw compound tags cannot be modified");
        }
        if (is_null($offset) && is_null($value->getName())) {
            throw new Exception("Tags inside a CompoundTag must be named.");
        }
        if (!is_null($offset)) {
            $value->setName($offset);
        } else {
            $offset = $value->getName();
        }
        $value->setParentTag($this);
        $this->offsetUnset($offset);
        $this->valueArray[] = $value;
    }

    /**
     * @inheritDoc
     */
    public function current(): bool|Tag
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
    public function key(): ?string
    {
        return $this->current()->getName();
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
        foreach ($this->valueArray as $val) {
            if ($val->getName() === $offset) {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset): ?Tag
    {
        foreach ($this->valueArray as $val) {
            if ($val->getName() === $offset) {
                return $val;
            }
        }
        return null;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function offsetUnset($offset): void
    {
        if($this->isRaw()) {
            throw new Exception("Raw compound tags cannot be modified");
        }
        foreach ($this->valueArray as $i => $val) {
            if ($val->getName() === $offset) {
                $val->setParentTag(null);
                unset($this->valueArray[$i]);
                break;
            }
        }
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
        if($this->isRaw()) {
            return strlen($this->rawContent) . " bytes";
        }
        return $this->count() . " entr" . ($this->count() === 1 ? "y" : "ies") . "\n{\n" .
            $this->indent(implode(", \n", array_map(strval(...), array_values($this->valueArray)))) .
            "\n}";
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        $data = [];
        foreach ($this->valueArray as $value) {
            $data[$value->getName()] = $value;
        }
        return $data;
    }

    /**
     * Set a child tag by name
     * If $name is null, the existing name of the tag object will be used
     *
     * @param string|null $name
     * @param Tag $tag
     * @return $this
     * @throws Exception
     */
    public function set(?string $name, Tag $tag): static
    {
        $this->offsetSet($name, $tag);
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     * @throws Exception
     */
    public function delete(string $name): static
    {
        $this->offsetUnset($name);
        return $this;
    }

    /**
     * Get a child tag by name
     *
     * @param string $name
     * @return Tag|null
     */
    public function get(string $name): ?Tag
    {
        return $this->offsetGet($name);
    }

    /**
     * @param string $name
     * @return ByteArrayTag|null
     */
    public function getByteArray(string $name): ?ByteArrayTag
    {
        $tag = $this->get($name);
        return $tag instanceof ByteArrayTag ? $tag : null;
    }

    /**
     * @param string $name
     * @return ByteTag|null
     */
    public function getByte(string $name): ?ByteTag
    {
        $tag = $this->get($name);
        return $tag instanceof ByteTag ? $tag : null;
    }

    /**
     * @param string $name
     * @return CompoundTag|null
     */
    public function getCompound(string $name): ?CompoundTag
    {
        $tag = $this->get($name);
        return $tag instanceof CompoundTag ? $tag : null;
    }

    /**
     * @param string $name
     * @return DoubleTag|null
     */
    public function getDouble(string $name): ?DoubleTag
    {
        $tag = $this->get($name);
        return $tag instanceof DoubleTag ? $tag : null;
    }

    /**
     * @param string $name
     * @return FloatTag|null
     */
    public function getFloat(string $name): ?FloatTag
    {
        $tag = $this->get($name);
        return $tag instanceof FloatTag ? $tag : null;
    }

    /**
     * @param string $name
     * @return IntArrayTag|null
     */
    public function getIntArray(string $name): ?IntArrayTag
    {
        $tag = $this->get($name);
        return $tag instanceof IntArrayTag ? $tag : null;
    }

    /**
     * @param string $name
     * @return IntTag|null
     */
    public function getInt(string $name): ?IntTag
    {
        $tag = $this->get($name);
        return $tag instanceof IntTag ? $tag : null;
    }

    /**
     * @param string $name
     * @param int|null $listContentTag - Required content type for the list, if null, any type can be returned
     * @return ListTag|null
     */
    public function getList(string $name, ?int $listContentTag = null): ?ListTag
    {
        $tag = $this->get($name);
        if (!$tag instanceof ListTag || ($listContentTag !== null && $tag->getContentTag() !== $listContentTag)) {
            return null;
        }
        return $tag;
    }

    /**
     * @param string $name
     * @return LongArrayTag|null
     */
    public function getLongArray(string $name): ?LongArrayTag
    {
        $tag = $this->get($name);
        return $tag instanceof LongArrayTag ? $tag : null;
    }

    /**
     * @param string $name
     * @return LongTag|null
     */
    public function getLong(string $name): ?LongTag
    {
        $tag = $this->get($name);
        return $tag instanceof LongTag ? $tag : null;
    }

    /**
     * @param string $name
     * @return ShortTag|null
     */
    public function getShort(string $name): ?ShortTag
    {
        $tag = $this->get($name);
        return $tag instanceof ShortTag ? $tag : null;
    }

    /**
     * @param string $name
     * @return StringTag|null
     */
    public function getString(string $name): ?StringTag
    {
        $tag = $this->get($name);
        return $tag instanceof StringTag ? $tag : null;
    }

    /**
     * @inheritDoc
     */
    function equals(Tag $tag): bool
    {
        if ($tag === $this) {
            return true;
        }
        if (!$tag instanceof CompoundTag || $this->getType() !== $tag->getType() || count($tag) !== count($this)) {
            return false;
        }
        /**
         * @var string $key
         * @var Tag $val
         */
        foreach ($this as $key => $val) {
            if (!isset($tag[$key]) || !$val->equals($tag[$key])) {
                return false;
            }
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function toSNBT(): string
    {
        $data = [];
        foreach ($this->valueArray as $value) {
            $data[] = StringTag::encodeSNBTString($value->getName()) . ": " . $value->toSNBT();
        }
        return "{" . implode(", ", $data) . "}";
    }
}
