<?php

declare(strict_types=1);

namespace Aternos\Nbt\IO\Writer;

use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\Serializer\NbtSerializer;

abstract class AbstractWriter implements Writer
{
    protected NbtFormat $format = NbtFormat::JAVA_EDITION;
    protected ?NbtSerializer $serializer = null;

    /**
     * @inheritDoc
     */
    public function getSerializer(): NbtSerializer
    {
        return $this->serializer ??= $this->getFormat()->createSerializer($this);
    }

    public function getFormat(): NbtFormat
    {
        return $this->format;
    }

    /**
     * @param int $format
     * @return $this
     */
    public function setFormat(NbtFormat $format): static
    {
        $this->format = $format;
        $this->serializer = null;
        return $this;
    }
}
