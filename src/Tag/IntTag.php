<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Override;

class IntTag extends IntValueTag
{
    public const TYPE = TagType::TAG_Int;

    #[Override]
    public function writeContent(Writer $writer): static
    {
        $writer->getSerializer()->writeInt($this->value);
        return $this;
    }

    #[Override]
    protected function readContent(Reader $reader): static
    {
        $this->value = $reader->getDeserializer()->readInt()->getValue();
        return $this;
    }

    #[Override]
    protected static function readContentRaw(Reader $reader, TagOptions $options): string
    {
        return $reader->getDeserializer()->readInt()->getRawData();
    }

    #[Override]
    public function toSNBT(): string
    {
        return strval($this->value);
    }
}
