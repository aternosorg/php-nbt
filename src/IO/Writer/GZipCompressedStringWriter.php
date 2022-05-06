<?php

namespace Aternos\Nbt\IO\Writer;

use Exception;

class GZipCompressedStringWriter extends StringWriter
{
    /**
     * @throws Exception
     */
    public function getStringData(): string
    {
        if (($compressed = @gzencode(parent::getStringData())) === false) {
            throw new Exception("Failed to apply GZip compression");
        }
        return $compressed;
    }
}
