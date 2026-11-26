<?php

declare(strict_types=1);

namespace Aternos\Nbt\IO\Writer;

use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\Serializer\NbtSerializer;
use Override;

abstract class AbstractWriter implements Writer
{
    protected int $format = NbtFormat::JAVA_EDITION;
    protected ?NbtSerializer $serializer = null;

    #[Override]
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
        $this->serializer = null;
        return $this;
    }
}
