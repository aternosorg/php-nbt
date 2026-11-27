<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\Deserializer\DeserializerFloatReadResult;
use Aternos\Nbt\Deserializer\NbtDeserializer;
use Aternos\Nbt\Exception\NbtException;
use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\Serializer\NbtSerializer;

/**
 * Keeps the bytes a value was read from, so that unchanged values (including NaN payloads) are written back exactly
 */
abstract class FloatValueTag extends Tag
{
    protected float $value = 0;
    protected ?string $rawValue = null;
    protected ?NbtFormat $rawValueFormat = null;

    /**
     * @param NbtDeserializer $deserializer
     * @return DeserializerFloatReadResult
     * @throws NbtException
     */
    abstract protected static function readNumber(NbtDeserializer $deserializer): DeserializerFloatReadResult;

    /**
     * @param NbtSerializer $serializer
     * @param float $value
     * @return void
     * @throws NbtException
     */
    abstract protected static function writeNumber(NbtSerializer $serializer, float $value): void;

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * @param float $value
     * @return $this
     */
    public function setValue(float $value): static
    {
        $this->value = $value;
        $this->rawValue = null;
        $this->rawValueFormat = null;
        return $this;
    }

    /**
     * The bytes the value was read from, null if the value was set or changed since
     *
     * @return string|null
     */
    public function getRawValue(): ?string
    {
        return $this->rawValue;
    }

    /**
     * @inheritDoc
     */
    public function writeContent(Writer $writer): static
    {
        if ($this->rawValue !== null && $this->rawValueFormat === $writer->getFormat()) {
            $writer->write($this->rawValue);
            return $this;
        }
        static::writeNumber($writer->getSerializer(), $this->value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function readContent(Reader $reader): static
    {
        $result = static::readNumber($reader->getDeserializer());
        $this->value = $result->getValue();
        $this->rawValue = $result->getRawData();
        $this->rawValueFormat = $reader->getFormat();
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected static function readContentRaw(Reader $reader, TagOptions $options): string
    {
        return static::readNumber($reader->getDeserializer())->getRawData();
    }

    /**
     * @inheritDoc
     */
    protected function getValueString(): string
    {
        return strval($this->value);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): int|float
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function equals(Tag $tag): bool
    {
        return $tag instanceof FloatValueTag && $this->getType() === $tag->getType() &&
            $tag->getValue() === $this->getValue();
    }
}
