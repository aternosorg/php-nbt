<?php

namespace Aternos\Nbt\IO\Writer;

class GZipCompressedStringWriter extends StringWriter
{
    public function getStringData(): string
    {
        return gzencode(parent::getStringData());
    }
}
