<?php

namespace Aternos\Nbt\IO\Reader;

class ZLibCompressedStringReader extends StringReader
{
    public function __construct(string $data, int $format)
    {
        parent::__construct(zlib_decode($data), $format);
    }
}