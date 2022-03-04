<?php


namespace Aternos\Nbt\Tag;


use Aternos\Nbt\IO\Reader\Reader;
use Aternos\Nbt\Serializer\NbtSerializer;
use Exception;

class LongTag extends IntValueTag
{
    use RawValueTag;

    public const TYPE = TagType::TAG_Long;

    /**
     * @inheritDoc
     */
    public function generatePayload(NbtSerializer $serializer): string
    {
        if($this->rawValueValid($serializer->getFormat())) {
            return $this->rawValue;
        }
        return $serializer->encodeLong($this->value);
    }

    /**
     * @inheritDoc
     */
    public function setValue(int $value): IntValueTag
    {
        $this->resetRawValue();
        return parent::setValue($value);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function readPayload(Reader $reader): Tag
    {
        $result = $reader->getSerializer()->readLong($reader);
        $this->setRawDataFromSerializer($result, $reader->getFormat());
        $this->value = $result->getValue();
        return $this;
    }
}
