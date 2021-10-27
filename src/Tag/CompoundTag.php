<?php

namespace Aternos\Nbt\Tag;

use ArrayAccess;
use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\Serializer\NbtSerializer;
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

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function generatePayload(NbtSerializer $serializer): string
    {
        $writtenNames = [];
        $res = "";
        foreach ($this->valueArray as $value) {
            if(in_array($value->getName(), $writtenNames)) {
                throw new Exception("Duplicate key '" . $value->getName() . "' in compound tag");
            }
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
            $this->valueArray[] = $tag;
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
        $this->offsetUnset($offset);
        $this->valueArray[] = $value;
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
    public function rewind()
    {
        reset($this->valueArray);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        foreach ($this->valueArray as $val) {
            if($val->getName() === $offset) {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        foreach ($this->valueArray as $val) {
            if($val->getName() === $offset) {
                return $val;
            }
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        foreach ($this->valueArray as $i => $val) {
            if($val->getName() === $offset) {
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
        return $this->count() . " entr" . ($this->count() === 1 ? "y" : "ies") . "\n{\n" .
            $this->indent(implode(", \n", array_map("strval", array_values($this->valueArray)))) .
            "\n}";
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $data = [];
        foreach ($this->valueArray as $value) {
            $data[$value->getName()] = $value;
        }
        return $data;
    }
}