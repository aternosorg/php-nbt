<?php


namespace Aternos\Nbt\Tag;


use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\IO\Writer\Writer;

class LongTag extends IntValueTag
{
    use RawValueTag;

    public const TYPE = TagType::TAG_Long;

    /**
     * @inheritDoc
     */
    public function writeContent(Writer $writer): static
    {
        if ($this->rawValueValid($writer->getFormat())) {
            $writer->write($this->rawValue);
            return $this;
        }
        $writer->getSerializer()->writeLong($this->value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function readContent(Reader $reader): static
    {
        $result = $reader->getDeserializer()->readLong();
        $this->setRawDataFromSerializer($result, $reader->getFormat());
        $this->value = $result->getValue();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setValue(int $value): IntValueTag
    {
        $this->resetRawValue();
        return parent::setValue($value);
    }
}
