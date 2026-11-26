<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;
use Override;

class DoubleTag extends FloatValueTag
{
    use RawValueTag;

    public const TYPE = TagType::TAG_Double;

    #[Override]
    public function writeContent(Writer $writer): static
    {
        if ($this->rawValueValid($writer->getFormat())) {
            $writer->write($this->rawValue);
            return $this;
        }
        $writer->getSerializer()->writeDouble($this->value);
        return $this;
    }

    #[Override]
    protected function readContent(Reader $reader): static
    {
        $result = $reader->getDeserializer()->readDouble();
        $this->setRawDataFromSerializer($result, $reader->getFormat());
        $this->value = $result->getValue();
        return $this;
    }

    #[Override]
    protected static function readContentRaw(Reader $reader, TagOptions $options): string
    {
        return $reader->getDeserializer()->readDouble()->getRawData();
    }

    #[Override]
    public function setValue(float $value): static
    {
        $this->resetRawValue();
        return parent::setValue($value);
    }

    #[Override]
    public function toSNBT(): string
    {
        return $this->value . "d";
    }
}
