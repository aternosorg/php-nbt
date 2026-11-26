<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\Deserializer\DeserializerFloatReadResult;
use Aternos\Nbt\Deserializer\NbtDeserializer;
use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\Serializer\NbtSerializer;
use Override;

/**
 * Keeps the bytes a value was read from, so that unchanged values (including NaN payloads) are written back exactly
 */
abstract class FloatValueTag extends Tag
{
    protected float $value = 0;
    protected ?string $rawValue = null;
    protected ?NbtFormat $rawValueFormat = null;

    abstract protected static function readNumber(NbtDeserializer $deserializer): DeserializerFloatReadResult;

    abstract protected static function writeNumber(NbtSerializer $serializer, float $value): void;

    public function getValue(): float
    {
        return $this->value;
    }

    public function setValue(float $value): static
    {
        $this->value = $value;
        $this->rawValue = null;
        $this->rawValueFormat = null;
        return $this;
    }

    /**
     * The bytes the value was read from, null if the value was set or changed since
     */
    public function getRawValue(): ?string
    {
        return $this->rawValue;
    }

    #[Override]
    protected function writeContent(Writer $writer): void
    {
        if ($this->rawValue !== null && $this->rawValueFormat === $writer->getFormat()) {
            $writer->write($this->rawValue);
            return;
        }
        static::writeNumber($writer->getSerializer(), $this->value);
    }

    #[Override]
    protected function readContent(Reader $reader): void
    {
        $result = static::readNumber($reader->getDeserializer());
        $this->value = $result->value;
        $this->rawValue = $result->rawData;
        $this->rawValueFormat = $reader->getFormat();
    }

    #[Override]
    protected static function readContentRaw(Reader $reader, TagOptions $options): string
    {
        return static::readNumber($reader->getDeserializer())->rawData;
    }

    #[Override]
    protected function getValueString(): string
    {
        return (string)$this->value;
    }

    #[Override]
    public function jsonSerialize(): float
    {
        return $this->value;
    }

    #[Override]
    public function equals(Tag $tag): bool
    {
        return $tag instanceof FloatValueTag && $this->getType() === $tag->getType() && $tag->value === $this->value;
    }
}
