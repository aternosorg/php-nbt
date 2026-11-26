<?php

declare(strict_types=1);

namespace Aternos\Nbt\IO\Writer;

use Aternos\Nbt\Exception\CompressionException;
use Override;

class GZipCompressedStringWriter extends StringWriter
{
    /**
     * @throws CompressionException
     */
    #[Override]
    public function getStringData(): string
    {
        $compressed = @gzencode(parent::getStringData());
        if ($compressed === false) {
            throw new CompressionException("Failed to apply GZip compression");
        }
        return $compressed;
    }
}
