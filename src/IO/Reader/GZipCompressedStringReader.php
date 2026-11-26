<?php

declare(strict_types=1);

namespace Aternos\Nbt\IO\Reader;

use Aternos\Nbt\Exception\CompressionException;
use Aternos\Nbt\NbtFormat;

class GZipCompressedStringReader extends StringReader
{
    /**
     * @throws CompressionException
     */
    public function __construct(string $data, NbtFormat $format, int $maxDecompressedSize = 0)
    {
        if (($uncompressed = @gzdecode($data, $maxDecompressedSize)) === false) {
            throw new CompressionException("Invalid GZip data");
        }
        parent::__construct($uncompressed, $format);
    }
}
