<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;

class ShortTag extends IntValueTag
{
    public const TagType TYPE = TagType::SHORT;
    public const int MIN_VALUE = -0x8000;
    public const int MAX_VALUE = 0x7fff;

    /**
     * @inheritDoc
     */
    public function writeContent(Writer $writer): static
    {
        $writer->getSerializer()->writeShort($this->value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function readContent(Reader $reader): static
    {
        $this->value = $reader->getDeserializer()->readShort()->getValue();
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected static function readContentRaw(Reader $reader, TagOptions $options): string
    {
        return $reader->getDeserializer()->readShort()->getRawData();
    }

    /**
     * @inheritDoc
     */
    public function toSNBT(): string
    {
        return $this->value . "s";
    }
}
