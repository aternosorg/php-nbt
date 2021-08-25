<?php

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\Serializer\NbtSerializer;
use Exception;

class CompoundTag extends Tag implements \Iterator, \ArrayAccess, \Countable
{
    public const TYPE = TagType::TAG_Compound;

    protected array $valueArray = [];

    /**
     * @inheritDoc
     */
    public function generatePayload(NbtSerializer $serializer): string
    {
        $res = "";
        /** @var Tag $value */
        foreach ($this->valueArray as $value) {
            $res .= $value->serialize($serializer, true);
        }
        $res .= (new EndTag())->serialize($serializer);
        return $res;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function readPayload(Reader $reader): Tag
    {
        while (!(($tag = Tag::load($reader)) instanceof EndTag)) {
            $this->valueArray[$tag->getName()] = $tag;
        }
        return $this;
    }

    /**
     * @param Tag $value
     * @inheritDoc
     * @throws Exception
     */
    public function offsetSet($offset, $value): void
    {
        if(!($value instanceof Tag) || $value instanceof EndTag){
            throw new Exception("Invalid CompoundTag value");
        }
        if(!is_string($offset) && !is_null($offset)) {
            throw new Exception("Invalid CompoundTag key");
        }
        if(is_null($offset) && is_null($value->getName())) {
            throw new Exception("Tags inside a CompoundTag must be named.");
        }
        if(!is_null($offset)) {
            $value->setName($offset);
        }else {
            $offset = $value->getName();
        }
        $this->valueArray[$offset] = $value;
    }

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
     * @inheritDoc
     */
    protected function getValueString(): string
    {
        return $this->count() . " entries\n{\n" . $this->indent(implode(", \n", array_map("strval", array_values($this->valueArray)))) . "\n}";
    }
}