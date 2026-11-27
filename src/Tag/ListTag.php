<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\Exception\InvalidTagValueException;
use Aternos\Nbt\Exception\NbtException;
use Aternos\Nbt\Exception\RawTagModificationException;
use Aternos\Nbt\Exception\UnknownTagTypeException;
use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Aternos\Nbt\NbtFormat;

/**
 * A list of unnamed tags that all have the same type
 *
 * @extends ArrayValueTag<Tag>
 */
class ListTag extends ArrayValueTag
{
    public const TagType TYPE = TagType::LIST;
    protected TagType $contentType = TagType::END;

    protected ?int $rawContentLength = null;
    protected ?string $rawContent = null;
    protected ?NbtFormat $rawContentFormat = null;

    /**
     * @inheritDoc
     * @throws NbtException
     */
    public function writeContent(Writer $writer): static
    {
        $writer->getSerializer()->writeByte($this->contentType->value);
        if ($this->isRaw()) {
            if($this->rawContentFormat !== $writer->getFormat()) {
                throw new RawTagModificationException("Cannot change format of raw list tag");
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
        return "";
    }

    /**
     * The type of the list entries, TagType::END while the list is empty and has no type
     */
    public function getContentType(): TagType
    {
        return $this->contentType;
    }

    /**
     * @param int $contentTagType
     * @return $this
     * @throws NbtException
     */
    public function setContentType(TagType $contentType): static
    {
        if ($this->isRaw()) {
            throw new RawTagModificationException("Raw list tags cannot be modified");
        }

        /** @var Tag $value */
        foreach ($this->valueArray as $value) {
            if ($value->getType() !== $contentType) {
                throw new InvalidTagValueException("New list content type is incompatible with its values");
            }
        }
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * @inheritDoc
     * @throws NbtException
     */
    protected function readValues(Reader $reader, int $length): array
    {
        $values = [];
        $tagClass = $this->contentType->getTagClass();
        for ($i = 0; $i < $length; $i++) {
            $values[] = new $tagClass($this->options)->setParentTag($this)->read($reader, false);
        }
        return $values;
    }

    /**
     * Accepts tags of the content type, or any tag if the list is empty and has no content type yet
     */
    protected function checkValue(mixed $value): void
    {
        if (!$value instanceof Tag) {
            throw new InvalidTagValueException("Invalid value of type " . get_debug_type($value) . " for " . $this->getType()->getName());
        }
        if ($this->contentType === TagType::END && $this->count() === 0) {
            return;
        }
        if ($value->getType() !== $this->contentType) {
            throw new InvalidTagValueException("Cannot add " . $value->getType()->getName() . " to " . $this->getType()->getName() .
                " with content type " . $this->contentType->getName());
        }
    }

    /**
     * @inheritDoc
     * @throws NbtException
     */
    protected function readContent(Reader $reader): static
    {
        $this->contentType = static::getContentTypeById($reader->getDeserializer()->readByte()->getValue());
        $length = $reader->getDeserializer()->readLengthPrefix()->getValue();
        $maxLength = $this->options->getMaxListTagLength();
        if ($maxLength !== null && $length > $maxLength) {
            $this->rawContentFormat = $reader->getFormat();
            $this->rawContentLength = $length;
            $this->rawContent = static::readValueTagsRaw($reader, $this->options, $this->contentType, $length);
            return $this;
        }
        $this->valueArray = $this->readValues($reader, $length);
        return $this;
    }

    /**
     * @inheritDoc
     * @throws NbtException
     */
    protected static function readContentRaw(Reader $reader, TagOptions $options): string
    {
        $contentTypeByte = $reader->getDeserializer()->readByte();
        $contentType = static::getContentTypeById($contentTypeByte->getValue());
        $length = $reader->getDeserializer()->readLengthPrefix();

        return $contentTypeByte->getRawData() . $length->getRawData() .
            static::readValueTagsRaw($reader, $options, $contentType, $length->getValue());
    }

    /**
     * @throws UnknownTagTypeException
     */
    protected static function getContentTypeById(int $typeId): TagType
    {
        return TagType::tryFrom($typeId) ?? throw new UnknownTagTypeException("Unknown ListTag content type " . $typeId);
    }

    /**
     * @param Reader $reader
     * @param TagOptions $options
     * @param int $contentType
     * @param int $length
     * @return string
     * @throws NbtException
     */
    protected static function readValueTagsRaw(Reader $reader, TagOptions $options, TagType $contentType, int $length): string
    {
        $valueData = "";
        $tagClass = $contentType->getTagClass();
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
    protected function getTagTypeString(): string
    {
        return parent::getTagTypeString() . "<" . $this->contentType->getName() . ">";
    }

    /**
     * @param Tag $value
     * @throws NbtException
     */
    public function offsetSet($offset, $value): void
    {
        if ($this->isRaw()) {
            throw new RawTagModificationException("Raw list tags cannot be modified");
        }

        $previousValue = $this->offsetGet($offset);
        parent::offsetSet($offset, $value);
        if ($this->contentType === TagType::END) {
            $this->contentType = $value->getType();
        }
        $previousValue?->setParentTag(null);
        $value->setParentTag($this);
    }

    /**
     * @inheritDoc
     * @throws RawTagModificationException
     */
    public function offsetUnset($offset): void
    {
        if ($this->isRaw()) {
            throw new RawTagModificationException("Raw list tags cannot be modified");
        }

        $this->offsetGet($offset)?->setParentTag(null);
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
            $this->getContentType() !== $tag->getContentType() || count($tag) !== count($this)) {
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
