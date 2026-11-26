<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Exception;
use Override;

class StringTag extends Tag
{
    public const TYPE = TagType::TAG_String;

    protected string $value = "";

    /**
     * @param string $source
     * @return string
     */
    public static function encodeSNBTString(string $source): string
    {
        $quoteChar = '"';
        if (str_contains($source, '"') && !str_contains($source, "'")) {
            $quoteChar = "'";
        }

        $result = "";
        for ($i = 0; $i < strlen($source); $i++) {
            $char = $source[$i];
            if ($char === "\\") {
                $result .= "\\\\";
            } else if ($char === $quoteChar) {
                $result .= "\\" . $quoteChar;
            } else {
                $result .= $char;
            }
        }

        return $quoteChar . $result . $quoteChar;
    }

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
     * @throws Exception
     */
    #[Override]
    public function writeContent(Writer $writer): static
    {
        $length = strlen($this->value);
        if ($length > 0xffff) {
            throw new Exception("String exceeds maximum length of " . 0xffff . " characters");
        }
        $writer->getSerializer()->writeString($this->value);
        return $this;
    }

    #[Override]
    protected function readContent(Reader $reader): static
    {
        $this->value = $reader->getDeserializer()->readString()->getValue();
        return $this;
    }

    #[Override]
    protected static function readContentRaw(Reader $reader, TagOptions $options): string
    {
        $length = $reader->getDeserializer()->readStringLengthPrefix();
        return $length->getRawData() . $reader->read($length->getValue());
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
        return $tag instanceof StringTag && $this->getType() === $tag->getType() &&
            $tag->getValue() === $this->getValue();
    }

    #[Override]
    public function toSNBT(): string
    {
        return static::encodeSNBTString($this->value);
    }
}
