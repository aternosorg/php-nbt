<?php

namespace Aternos\Nbt\IO\Reader;

use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\Serializer\NbtSerializer;

abstract class AbstractReader implements Reader
{
    protected int $format = NbtFormat::JAVA_EDITION;
    protected ?NbtSerializer $serializer = null;

    /**
     * @inheritDoc
     */
    public function getSerializer(): NbtSerializer
    {
        if(is_null($this->serializer)) {
            $this->serializer = NbtFormat::getSerializer($this->getFormat());
        }
        return $this->serializer;
    }

    /**
     * @return int
     */
    public function getFormat(): int
    {
        return $this->format;
    }

    /**
     * @param int $format
     * @return AbstractReader
     */
    public function setFormat(int $format): AbstractReader
    {
        $this->format = $format;
        return $this;
    }
}