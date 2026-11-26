<?php

declare(strict_types=1);

namespace Aternos\Nbt\IO\Reader;

use Aternos\Nbt\Deserializer\NbtDeserializer;
use Aternos\Nbt\NbtFormat;
use Override;

abstract class AbstractReader implements Reader
{
    protected NbtFormat $format = NbtFormat::JavaEdition;
    protected ?NbtDeserializer $deserializer = null;

    #[Override]
    public function getDeserializer(): NbtDeserializer
    {
        return $this->deserializer ??= $this->getFormat()->createDeserializer($this);
    }

    #[Override]
    public function getFormat(): NbtFormat
    {
        return $this->format;
    }

    public function setFormat(NbtFormat $format): static
    {
        $this->format = $format;
        $this->deserializer = null;
        return $this;
    }
}
