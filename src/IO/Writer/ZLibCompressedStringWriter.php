<?php

namespace Aternos\Nbt\IO\Writer;

use Exception;

class ZLibCompressedStringWriter extends StringWriter
{
    /**
     * @throws Exception
     */
    public function getStringData(): string
    {
        if (($compressed = @zlib_encode(parent::getStringData(), ZLIB_ENCODING_DEFLATE)) === false) {
            throw new Exception("Failed to apply ZLib compression");
        }
        return $compressed;
    }
}
