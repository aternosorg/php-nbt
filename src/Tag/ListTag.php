<?php

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Exception;

class ListTag extends ArrayValueTag
{
    public const TYPE = TagType::TAG_List;
    protected int $contentTagType = TagType::TAG_End;

    /**
     * @inheritDoc
     */
    public function writeContent(Writer $writer): static
    {
        $writer->getSerializer()->writeByte($this->contentTagType)->writeLengthPrefix($this->count());
        $this->writeValues($writer);
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function writeValues(Writer $writer): string
    {
        /** @var Tag $value */
        foreach ($this->valueArray as $value) {
            $value->writeContent($writer);
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getContentTag(): int
    {
        return $this->contentTagType;
    }

    /**
     * @param int $contentTagType
     * @return ListTag
     * @throws Exception
     */
    public function setContentTag(int $contentTagType): ListTag
    {
        /** @var Tag $value */
        foreach ($this->valueArray as $value) {
            if ($value::TYPE !== $contentTagType) {
                throw new Exception("New list content type is incompatible with its values");
            }
        }
        $this->contentTagType = $contentTagType;
        return $this;
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
        if (is_null($tagClass)) {
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
        if (!($value instanceof Tag)) {
            return false;
        }
        if ($this->count() === 0 && $this->contentTagType === TagType::TAG_End) {
            $this->contentTagType = $value::TYPE;
            return true;
        }
        return $value::TYPE === $this->contentTagType;
    }

    protected function readContent(Reader $reader): static
    {
        $this->contentTagType = $reader->getDeserializer()->readByte()->getValue();
        return parent::readContent($reader);
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

    /**
     * @inheritDoc
     */
    public function equals(Tag $tag): bool
    {
        if ($tag === $this) {
            return true;
        }
        if (!$tag instanceof ListTag || $this->getType() !== $tag->getType() ||
            $this->getContentTag() !== $tag->getContentTag() || count($tag) !== count($this)) {
            return false;
        }
        /**
         * @var int $i
         * @var Tag $val
         */
        foreach ($this as $i => $val) {
            if (!$val->equals($tag[$i])) {
                return false;
            }
        }
        return true;
    }
}
