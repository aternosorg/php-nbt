<?php

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\Serializer\NbtSerializer;

class StringTag extends Tag
{
    public const TYPE = TagType::TAG_String;

    protected string $value;

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return StringTag
     */
    public function setValue(string $value): StringTag
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
     * @throws \Exception
     */
    public function generatePayload(NbtSerializer $serializer): string
    {
        $length = strlen($this->value);
        if($length > 0xffff) {
            throw new \Exception("String exceeds maximum length of " . 0xffff . " characters");
        }
        return $serializer->encodeStringLengthPrefix($length) . $this->value;
    }

    /**
     * @inheritDoc
     */
    protected function readPayload(Reader $reader): Tag
    {
        $length = $reader->getSerializer()->readStringLengthPrefix($reader)->getValue();
        $this->value = $reader->read($length);
        return $this;
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
}