<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;


use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Override;

class LongTag extends IntValueTag
{
    use RawValueTag;

    public const TYPE = TagType::TAG_Long;

    #[Override]
    public function writeContent(Writer $writer): static
    {
        if ($this->rawValueValid($writer->getFormat())) {
            $writer->write($this->rawValue);
            return $this;
        }
        $writer->getSerializer()->writeLong($this->value);
        return $this;
    }

    #[Override]
    protected function readContent(Reader $reader): static
    {
        $result = $reader->getDeserializer()->readLong();
        $this->setRawDataFromSerializer($result, $reader->getFormat());
        $this->value = $result->getValue();
        return $this;
    }

    #[Override]
    protected static function readContentRaw(Reader $reader, TagOptions $options): string
    {
        return $reader->getDeserializer()->readLong()->getRawData();
    }

    #[Override]
    public function setValue(int $value): static
    {
        $this->resetRawValue();
        return parent::setValue($value);
    }

    #[Override]
    public function toSNBT(): string
    {
        return $this->value . "L";
    }
}
