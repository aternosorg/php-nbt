<?php

namespace Aternos\Nbt\IO\Reader;

class CompressedStringReader extends StringReader
{
    public function __construct(string $data, int $format)
    {
        parent::__construct(gzdecode($data), $format);
    }
}
