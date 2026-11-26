<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

class RawTagReadResult
{
    public function __construct(protected string $data, protected TagType $tagType)
    {
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    public function getTagType(): TagType
    {
        return $this->tagType;
    }
}
