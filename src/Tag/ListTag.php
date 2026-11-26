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
use Override;

/**
 * A list of unnamed tags that all have the same type
 *
 * @extends ArrayValueTag<Tag>
 */
class ListTag extends ArrayValueTag
{
    public const TagType TYPE = TagType::List;

    /**
     * Type of the list entries, TagType::End for an empty list without a type
     */
    protected TagType $contentType = TagType::End;

    protected ?int $rawContentLength = null;
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

    public function getContentType(): TagType
    {
        return $this->contentType;
    }

    /**
     * Setting the content type is only necessary for empty lists,
     * otherwise it is set automatically when the first entry is added
     *
     * @throws NbtException if the list is raw or contains entries of a different type
     */
    public function setContentType(TagType $contentType): static
    {
        $this->assertNotRaw();
        if (!array_all($this->valueArray, fn(Tag $value): bool => $value->getType() === $contentType)) {
            throw new InvalidTagValueException("Cannot change content type of " . $this->describe() . " to " . $contentType->getName() .
                " because it contains entries of type " . $this->contentType->getName());
        }
        $this->contentType = $contentType;
        return $this;
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
        $serializer = $writer->getSerializer();
        $serializer->writeByte($this->contentType->value);
        if ($this->rawContent !== null && $this->rawContentLength !== null) {
            if ($this->rawContentFormat !== $writer->getFormat()) {
                throw new RawTagModificationException("Cannot change format of raw " . $this->describe());
            }
            $serializer->writeLengthPrefix($this->rawContentLength);
            $writer->write($this->rawContent);
            return;
        }

        $serializer->writeLengthPrefix($this->count());
        foreach ($this->valueArray as $value) {
            $value->writeGuardedContent($writer);
        }
    }

    #[Override]
    protected function readContent(Reader $reader): void
    {
        $deserializer = $reader->getDeserializer();
        $this->contentType = $this->readContentType($reader);
        $length = $deserializer->readLengthPrefix()->value;

        $maxLength = $this->options->getMaxListTagLength();
        if ($maxLength !== null && $length > $maxLength) {
            $this->rawContentFormat = $reader->getFormat();
            $this->rawContentLength = $length;
            $this->rawContent = static::readEntriesRaw($reader, $this->options, $this->contentType, $length);
            return;
        }

        $values = [];
        for ($i = 0; $i < $length; $i++) {
            $values[] = $this->contentType->createTag($this->options)
                ->setParentTag($this)
                ->read($reader, false);
        }
        $this->valueArray = $values;
    }

    #[Override]
    protected static function readContentRaw(Reader $reader, TagOptions $options): string
    {
        $deserializer = $reader->getDeserializer();
        $contentType = $deserializer->readByte();
        $length = $deserializer->readLengthPrefix();
        $tagType = TagType::tryFrom($contentType->value)
            ?? throw new UnknownTagTypeException("Unknown TAG_List content type " . $contentType->value);

        return $contentType->rawData . $length->rawData .
            static::readEntriesRaw($reader, $options, $tagType, $length->value);
    }

    /**
     * @throws UnknownTagTypeException
     */
    protected function readContentType(Reader $reader): TagType
    {
        $type = $reader->getDeserializer()->readByte()->value;
        return TagType::tryFrom($type)
            ?? throw new UnknownTagTypeException("Unknown content type " . $type . " in " . $this->describe());
    }

    /**
     * @throws NbtException
     */
    protected static function readEntriesRaw(Reader $reader, TagOptions $options, TagType $contentType, int $length): string
    {
        $tagClass = $contentType->getTagClass();
        $data = "";
        for ($i = 0; $i < $length; $i++) {
            $data .= $tagClass::readRaw($reader, $options, false);
        }
        return $data;
    }

    /**
     * Accepts tags of the content type, or any tag except TAG_End if the list is empty and has no content type yet
     */
    #[Override]
    protected function isValidValue(mixed $value): bool
    {
        if (!$value instanceof Tag) {
            return false;
        }
        if ($this->contentType === TagType::End && $this->count() === 0) {
            return !$value instanceof EndTag;
        }
        return $value->getType() === $this->contentType;
    }

    /**
     * @param Tag $value
     * @throws NbtException
     */
    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->assertNotRaw();
        $previousValue = is_int($offset) ? $this->valueArray[$offset] ?? null : null;
        parent::offsetSet($offset, $value);
        if ($this->contentType === TagType::End) {
            $this->contentType = $value->getType();
        }
        $previousValue?->setParentTag(null);
        $value->setParentTag($this);
    }

    /**
     * @throws NbtException
     */
    #[Override]
    public function offsetUnset(mixed $offset): void
    {
        $this->assertNotRaw();
        $this->offsetGet($offset)?->setParentTag(null);
        parent::offsetUnset($offset);
    }

    #[Override]
    protected function getEntryStrings(): array
    {
        return array_map(fn(Tag $value): string => $value->__toString(), $this->valueArray);
    }

    #[Override]
    protected function getTagTypeString(): string
    {
        return parent::getTagTypeString() . "<" . $this->contentType->getName() . ">";
    }

    #[Override]
    public function equals(Tag $tag): bool
    {
        if ($tag === $this) {
            return true;
        }
        if (!$tag instanceof ListTag || $this->contentType !== $tag->contentType || count($tag) !== count($this)) {
            return false;
        }
        foreach ($this->valueArray as $i => $value) {
            if (!$value->equals($tag->valueArray[$i])) {
                return false;
            }
        }
        return true;
    }

    #[Override]
    protected function getValueString(): string
    {
        if ($this->rawContent !== null) {
            return strlen($this->rawContent) . " bytes";
        }
        return parent::getValueString();
    }

    #[Override]
    public function toSNBT(): string
    {
        $values = array_map(fn(Tag $value): string => $value->toSNBT(), $this->valueArray);
        return "[" . implode(", ", $values) . "]";
    }
}
