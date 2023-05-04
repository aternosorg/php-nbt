<?php

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Exception;

class StringTag extends Tag
{
    public const TYPE = TagType::TAG_String;

    protected string $value = "";

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setValue(string $value): static
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return strlen($this->value);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function writeContent(Writer $writer): static
    {
        $length = strlen($this->value);
        if ($length > 0xffff) {
            throw new Exception("String exceeds maximum length of " . 0xffff . " characters");
        }
        $writer->getSerializer()->writeString($this->value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function readContent(Reader $reader): static
    {
        $this->value = $reader->getDeserializer()->readString()->getValue();
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected static function readContentRaw(Reader $reader, TagOptions $options): string
    {
        $length = $reader->getDeserializer()->readStringLengthPrefix();
        return $length->getRawData() . $reader->read($length->getValue());
    }

    /**
     * @inheritDoc
     */
    protected function getValueString(): string
    {
        return "'" . str_replace("\n", "\\n", $this->value) . "'";
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function equals(Tag $tag): bool
    {
        return $tag instanceof StringTag && $this->getType() === $tag->getType() &&
            $tag->getValue() === $this->getValue();
    }
}
