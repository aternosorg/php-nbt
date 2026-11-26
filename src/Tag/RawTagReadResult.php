<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

class RawTagReadResult
{
    public function __construct(protected string $data, protected int $tagType)
    {
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function getTagType(): int
    {
        return $this->tagType;
    }
}
