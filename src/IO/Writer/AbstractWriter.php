<?php

namespace Aternos\Nbt\IO\Writer;

use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\Serializer\NbtSerializer;

abstract class AbstractWriter implements Writer
{
    protected int $format = NbtFormat::JAVA_EDITION;
    protected ?NbtSerializer $serializer = null;

    /**
     * @inheritDoc
     */
    public function getSerializer(): NbtSerializer
    {
        if (is_null($this->serializer)) {
            $this->serializer = NbtFormat::getSerializer($this->getFormat(), $this);
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
     * @return $this
     */
    public function setFormat(int $format): static
    {
        $this->format = $format;
        $this->serializer = null;
        return $this;
    }
}
