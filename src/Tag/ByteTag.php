<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;

class ByteTag extends IntValueTag
{
    public const TagType TYPE = TagType::BYTE;
    public const int MIN_VALUE = -0x80;
    public const int MAX_VALUE = 0x7f;

    /**
     * @inheritDoc
     */
    public function writeContent(Writer $writer): static
    {
        $writer->getSerializer()->writeByte($this->value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function readContent(Reader $reader): static
    {
        $this->value = $reader->getDeserializer()->readByte()->getValue();
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected static function readContentRaw(Reader $reader, TagOptions $options): string
    {
        return $reader->getDeserializer()->readByte()->getRawData();
    }

    /**
     * @inheritDoc
     */
    public function toSNBT(): string
    {
        return $this->value . "b";
    }
}
