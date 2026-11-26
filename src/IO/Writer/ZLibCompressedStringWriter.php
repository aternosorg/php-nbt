<?php

declare(strict_types=1);

namespace Aternos\Nbt\IO\Writer;

use Aternos\Nbt\Exception\CompressionException;
use Override;

class ZLibCompressedStringWriter extends StringWriter
{
    /**
     * @throws CompressionException
     */
    #[Override]
    public function getStringData(): string
    {
        $compressed = @zlib_encode(parent::getStringData(), ZLIB_ENCODING_DEFLATE);
        if ($compressed === false) {
            throw new CompressionException("Failed to apply ZLib compression");
        }
        return $compressed;
    }
}
