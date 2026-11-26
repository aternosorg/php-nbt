<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Override;

class ByteTag extends IntValueTag
{
    public const TYPE = TagType::TAG_Byte;

    #[Override]
    public function writeContent(Writer $writer): static
    {
        $writer->getSerializer()->writeByte($this->value);
        return $this;
    }

    #[Override]
    protected function readContent(Reader $reader): static
    {
        $this->value = $reader->getDeserializer()->readByte()->getValue();
        return $this;
    }

    #[Override]
    protected static function readContentRaw(Reader $reader, TagOptions $options): string
    {
        return $reader->getDeserializer()->readByte()->getRawData();
    }

    #[Override]
    public function toSNBT(): string
    {
        return $this->value . "b";
    }
}
