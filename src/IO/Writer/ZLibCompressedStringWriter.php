<?php

declare(strict_types=1);

namespace Aternos\Nbt\IO\Writer;

use Exception;
use Override;

class ZLibCompressedStringWriter extends StringWriter
{
    /**
     * @throws Exception
     */
    #[Override]
    public function getStringData(): string
    {
        if (($compressed = @zlib_encode(parent::getStringData(), ZLIB_ENCODING_DEFLATE)) === false) {
            throw new Exception("Failed to apply ZLib compression");
        }
        return $compressed;
    }
}
