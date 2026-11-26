<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Override;

class ShortTag extends IntValueTag
{
    public const TYPE = TagType::TAG_Short;

    #[Override]
    public function writeContent(Writer $writer): static
    {
        $writer->getSerializer()->writeShort($this->value);
        return $this;
    }

    #[Override]
    protected function readContent(Reader $reader): static
    {
        $this->value = $reader->getDeserializer()->readShort()->getValue();
        return $this;
    }

    #[Override]
    protected static function readContentRaw(Reader $reader, TagOptions $options): string
    {
        return $reader->getDeserializer()->readShort()->getRawData();
    }

    #[Override]
    public function toSNBT(): string
    {
        return $this->value . "s";
    }
}
