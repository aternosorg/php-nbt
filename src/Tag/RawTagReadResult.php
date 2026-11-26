<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

final readonly class RawTagReadResult
{
    /**
     * The complete raw tag including type byte, name and payload
     */
    public string $data;

    public TagType $tagType;

    public function __construct(string $data, TagType $tagType)
    {
        $this->data = $data;
        $this->tagType = $tagType;
    }
}
