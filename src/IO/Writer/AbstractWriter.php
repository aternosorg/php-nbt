<?php

declare(strict_types=1);

namespace Aternos\Nbt\IO\Writer;

use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\Serializer\NbtSerializer;
use Override;

abstract class AbstractWriter implements Writer
{
    protected NbtFormat $format = NbtFormat::JavaEdition;
    protected ?NbtSerializer $serializer = null;

    #[Override]
    public function getSerializer(): NbtSerializer
    {
        return $this->serializer ??= $this->getFormat()->createSerializer($this);
    }

    #[Override]
    public function getFormat(): NbtFormat
    {
        return $this->format;
    }

    public function setFormat(NbtFormat $format): static
    {
        $this->format = $format;
        $this->serializer = null;
        return $this;
    }
}
