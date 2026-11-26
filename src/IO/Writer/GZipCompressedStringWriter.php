<?php

declare(strict_types=1);

namespace Aternos\Nbt\IO\Writer;

use Aternos\Nbt\Exception\CompressionException;

class GZipCompressedStringWriter extends StringWriter
{
    /**
     * @throws CompressionException
     */
    public function getStringData(): string
    {
        if (($compressed = @gzencode(parent::getStringData())) === false) {
            throw new CompressionException("Failed to apply GZip compression");
        }
        return $compressed;
    }
}
