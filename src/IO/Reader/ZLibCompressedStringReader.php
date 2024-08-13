<?php

namespace Aternos\Nbt\IO\Reader;

use Exception;

class ZLibCompressedStringReader extends StringReader
{
    /**
     * @throws Exception
     */
    public function __construct(string $data, int $format, int $maxDecompressedSize = 0)
    {
        if (($uncompressed = @zlib_decode($data, $maxDecompressedSize)) === false) {
            throw new Exception("Invalid ZLib data");
        }
        parent::__construct($uncompressed, $format);
    }
}
