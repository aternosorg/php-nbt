<?php

namespace Aternos\Nbt\IO\Writer;

class GZipCompressedStringWriter extends StringWriter
{
    public function getStringData(): string
    {
        return zlib_encode(parent::getStringData(), ZLIB_ENCODING_DEFLATE);
    }
}
