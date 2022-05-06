<?php

namespace Aternos\Nbt\IO\Reader;

use Exception;

class GZipCompressedStringReader extends StringReader
{
    /**
     * @throws Exception
     */
    public function __construct(string $data, int $format)
    {
        if (($uncompressed = @gzdecode($data)) === false) {
            throw new Exception("Invalid GZip data");
        }
        parent::__construct($uncompressed, $format);
    }
}
