<?php

declare(strict_types=1);

namespace Aternos\Nbt\IO\Reader;

use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\Deserializer\NbtDeserializer;

abstract class AbstractReader implements Reader
{
    protected NbtFormat $format = NbtFormat::JAVA_EDITION;
    protected ?NbtDeserializer $deserializer = null;

    /**
     * @inheritDoc
     */
    public function getDeserializer(): NbtDeserializer
    {
        return $this->deserializer ??= $this->getFormat()->createDeserializer($this);
    }

    public function getFormat(): NbtFormat
    {
        return $this->format;
    }

    /**
     * @param NbtFormat $format
     * @return $this
     */
    public function setFormat(NbtFormat $format): static
    {
        $this->format = $format;
        $this->deserializer = null;
        return $this;
    }
}
