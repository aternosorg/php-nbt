<?php

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Exception;

class ListTag extends ArrayValueTag
{
    public const TYPE = TagType::TAG_List;
    protected int $contentTagType = TagType::TAG_End;

    protected ?int $rawContentLength = null;
    protected ?string $rawContent = null;
    protected ?int $rawContentFormat = null;

    /**
     * @inheritDoc
     */
    public function writeContent(Writer $writer): static
    {
        $writer->getSerializer()->writeByte($this->contentTagType);
        if ($this->isRaw()) {
            if($this->rawContentFormat !== $writer->getFormat()) {
                throw new Exception("Cannot change format of raw list tag");
            }

            $writer->getSerializer()->writeLengthPrefix($this->rawContentLength);
            $writer->write($this->rawContent);
        } else {
            $writer->getSerializer()->writeLengthPrefix($this->count());
            $this->writeValues($writer);
        }
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
     * @return $this
     * @throws Exception
     */
    public function setContentTag(int $contentTagType): static
    {
        if ($this->isRaw()) {
            throw new Exception("Raw list tags cannot be modified");
        }

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
        /** @var class-string<Tag>|null $tagClass */
        $tagClass = Tag::getTagClass($this->contentTagType);
        if (is_null($tagClass)) {
            throw new Exception("Unknown ListTag content type " . $this->contentTagType);
        }
        for ($i = 0; $i < $length; $i++) {
            $values[] = (new $tagClass($this->options))->setParentTag($this)->read($reader, false);
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

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function readContent(Reader $reader): static
    {
        $this->contentTagType = $reader->getDeserializer()->readByte()->getValue();
        $length = $reader->getDeserializer()->readLengthPrefix()->getValue();
        $maxLength = $this->options->getMaxListTagLength();
        if ($maxLength !== null && $length > $maxLength) {
            $this->rawContentFormat = $reader->getFormat();
            $this->rawContentLength = $length;
            $this->rawContent = static::readValueTagsRaw($reader, $this->options, $this->contentTagType, $length);
            return $this;
        }
        $this->valueArray = $this->readValues($reader, $length);
        return $this;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected static function readContentRaw(Reader $reader, TagOptions $options): string
    {
        $contentTagType = $reader->getDeserializer()->readByte();
        $length = $reader->getDeserializer()->readLengthPrefix();

        return $contentTagType->getRawData() . $length->getRawData() .
            static::readValueTagsRaw($reader, $options, $contentTagType->getValue(), $length->getValue());
    }

    /**
     * @param Reader $reader
     * @param TagOptions $options
     * @param int $contentType
     * @param int $length
     * @return string
     * @throws Exception
     */
    protected static function readValueTagsRaw(Reader $reader, TagOptions $options, int $contentType, int $length): string
    {
        $valueData = "";

        /** @var class-string<Tag>|null $tagClass */
        $tagClass = Tag::getTagClass($contentType);
        if (is_null($tagClass)) {
            throw new Exception("Unknown ListTag content type " . $contentType);
        }
        for ($i = 0; $i < $length; $i++) {
            $valueData .= $tagClass::readRaw($reader, $options, false);
        }

        return $valueData;
    }

    /**
     * @return bool
     */
    public function isRaw(): bool
    {
        return $this->rawContent !== null;
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
    public function offsetSet($offset, $value): void
    {
        if ($this->isRaw()) {
            throw new Exception("Raw list tags cannot be modified");
        }

        /** @var Tag|null $previousValue */
        $previousValue = $this->valueArray[$offset] ?? null;
        parent::offsetSet($offset, $value);
        $value->setParentTag($this);
        $previousValue?->setParentTag(null);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function offsetUnset($offset): void
    {
        if ($this->isRaw()) {
            throw new Exception("Raw list tags cannot be modified");
        }

        /** @var Tag|null $previousValue */
        $previousValue = $this->valueArray[$offset] ?? null;
        $previousValue?->setParentTag(null);
        parent::offsetUnset($offset);
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

    /**
     * @inheritDoc
     */
    protected function getValueString(): string
    {
        if ($this->isRaw()) {
            return strlen($this->rawContent) . " bytes";
        }
        return parent::getValueString();
    }

    /**
     * @inheritDoc
     */
    public function toSNBT(): string
    {
        $values = [];
        foreach ($this->valueArray as $value) {
            $values[] = $value->toSNBT();
        }
        return "[" . implode(", ", $values) . "]";
    }
}
