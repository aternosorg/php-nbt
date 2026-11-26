<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

final readonly class RawTagReadResult
{
    /**
     * @param string $data the complete raw tag including type byte, name and payload
     */
    public function __construct(public string $data, public TagType $tagType)
    {
    }
}
