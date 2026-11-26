<?php

declare(strict_types=1);

namespace Aternos\Nbt\IO\Writer;

use Aternos\Nbt\Exception\CompressionException;

class ZLibCompressedStringWriter extends StringWriter
{
    /**
     * @throws CompressionException
     */
    public function getStringData(): string
    {
        if (($compressed = @zlib_encode(parent::getStringData(), ZLIB_ENCODING_DEFLATE)) === false) {
            throw new CompressionException("Failed to apply ZLib compression");
        }
        return $compressed;
    }
}
