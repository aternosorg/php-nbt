<?php

declare(strict_types=1);

namespace Aternos\Nbt\IO\Reader;

use Aternos\Nbt\Exception\CompressionException;
use Aternos\Nbt\NbtFormat;

class ZLibCompressedStringReader extends StringReader
{
    /**
     * @param int $maxDecompressedSize maximum size of the decompressed data in bytes, 0 for no limit
     * @throws CompressionException
     */
    public function __construct(string $data, NbtFormat $format, int $maxDecompressedSize = 0)
    {
        $decompressed = @zlib_decode($data, $maxDecompressedSize);
        if ($decompressed === false) {
            $reason = $maxDecompressedSize > 0 ? "invalid data or larger than " . $maxDecompressedSize . " bytes" : "invalid data";
            throw new CompressionException("Failed to decompress ZLib data: " . $reason);
        }
        parent::__construct($decompressed, $format);
    }
}
