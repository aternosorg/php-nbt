<?php

declare(strict_types=1);

namespace Aternos\Nbt\IO\Reader;

use Aternos\Nbt\Exception\CompressionException;
use Aternos\Nbt\NbtFormat;

class ZLibCompressedStringReader extends StringReader
{
    /**
     * @throws CompressionException
     */
    public function __construct(string $data, NbtFormat $format, int $maxDecompressedSize = 0)
    {
        if (($uncompressed = @zlib_decode($data, $maxDecompressedSize)) === false) {
            throw new CompressionException("Invalid ZLib data");
        }
        parent::__construct($uncompressed, $format);
    }
}
