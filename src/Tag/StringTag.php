<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Override;

class StringTag extends Tag
{
    public const TagType TYPE = TagType::String;

    protected string $value = "";

    /**
     * Quote a string for SNBT, preferring double quotes
     */
    public static function encodeSNBTString(string $source): string
    {
        $quoteChar = str_contains($source, '"') && !str_contains($source, "'") ? "'" : '"';
        $escaped = str_replace(["\\", $quoteChar], ["\\\\", "\\" . $quoteChar], $source);
        return $quoteChar . $escaped . $quoteChar;
    }

    #[Override]
    public function getType(): TagType
    {
        return self::TYPE;
    }

    /**
     * The UTF-8 encoded value
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value UTF-8 encoded, converted to the encoding of the NBT format when written
     */
    public function setValue(string $value): static
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Length of the UTF-8 encoded value in bytes
     */
    public function getLength(): int
    {
        return strlen($this->value);
    }

    #[Override]
    protected function writeContent(Writer $writer): void
    {
        $writer->getSerializer()->writeString($this->value);
    }

    #[Override]
    protected function readContent(Reader $reader): void
    {
        $this->value = $reader->getDeserializer()->readString()->value;
    }

    #[Override]
    protected static function readContentRaw(Reader $reader, TagOptions $options): string
    {
        return $reader->getDeserializer()->readRawString();
    }

    #[Override]
    protected function getValueString(): string
    {
        return "'" . str_replace("\n", "\\n", $this->value) . "'";
    }

    #[Override]
    public function jsonSerialize(): string
    {
        return $this->value;
    }

    #[Override]
    public function equals(Tag $tag): bool
    {
        return $tag instanceof StringTag && $tag->value === $this->value;
    }

    #[Override]
    public function toSNBT(): string
    {
        return static::encodeSNBTString($this->value);
    }
}
