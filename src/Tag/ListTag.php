<?php

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\Serializer\NbtSerializer;
use Exception;

class ListTag extends ArrayValueTag
{
    public const TYPE = TagType::TAG_List;

    protected int $contentTagType = TagType::TAG_End;

    /**
     * @inheritDoc
     */
    public function generatePayload(NbtSerializer $serializer): string
    {
        return $serializer->encodeByte($this->contentTagType) . $serializer->encodeLengthPrefix($this->count()) . $this->generateValues($serializer);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function generateValues(NbtSerializer $serializer): string
    {
        $res = "";
        /** @var Tag $value */
        foreach ($this->valueArray as $value) {
            $res .= $value->generatePayload($serializer);
        }
        return $res;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function readValues(Reader $reader, int $length): array
    {
        $values = [];
        /** @var Tag $tagClass */
        $tagClass = Tag::getTagClass($this->contentTagType);
        if(is_null($tagClass)) {
            throw new Exception("Unknown ListTag content type " . $this->contentTagType);
        }
        for ($i = 0; $i < $length; $i++) {
            $values[] = (new $tagClass())->read($reader, false);
        }
        return $values;
    }

    /**
     * @inheritDoc
     */
    protected function checkArrayValue($value): bool
    {
        if(!($value instanceof Tag)) {
            return false;
        }
        if($this->count() === 0 && $this->contentTagType === TagType::TAG_End) {
            $this->contentTagType = $value::TYPE;
            return true;
        }
        return $value::TYPE === $this->contentTagType;
    }

    /**
     * @inheritDoc
     */
    protected function readPayload(Reader $reader): Tag
    {
        $this->contentTagType = $reader->getSerializer()->decodeByte($reader->read(1));
        return parent::readPayload($reader);
    }

    /**
     * @inheritDoc
     */
    protected function checkArrayKey($offset): bool
    {
        return is_int($offset);
    }

    /**
     * @inheritDoc
     */
    protected function getTagTypeString(): string
    {
        return parent::getTagTypeString() . "<" . TagType::NAMES[$this->contentTagType] . ">";
    }
}