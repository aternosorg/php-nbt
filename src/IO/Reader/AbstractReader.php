<?php

declare(strict_types=1);

namespace Aternos\Nbt\IO\Reader;

use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\Deserializer\NbtDeserializer;
use Override;

abstract class AbstractReader implements Reader
{
    protected int $format = NbtFormat::JAVA_EDITION;
    protected ?NbtDeserializer $deserializer = null;

    #[Override]
    public function getDeserializer(): NbtDeserializer
    {
        if (is_null($this->deserializer)) {
            $this->deserializer = NbtFormat::getDeserializer($this->getFormat(), $this);
        }
        return $this->deserializer;
    }

    /**
     * @return int
     */
    #[Override]
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
        $this->deserializer = null;
        return $this;
    }
}
