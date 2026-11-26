<?php

declare(strict_types=1);

namespace Aternos\Nbt\IO\Writer;

use Exception;
use Override;

class GZipCompressedStringWriter extends StringWriter
{
    /**
     * @throws Exception
     */
    #[Override]
    public function getStringData(): string
    {
        if (($compressed = @gzencode(parent::getStringData())) === false) {
            throw new Exception("Failed to apply GZip compression");
        }
        return $compressed;
    }
}
