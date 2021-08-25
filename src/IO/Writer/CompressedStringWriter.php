<?php

namespace Aternos\Nbt\IO\Writer;

class CompressedStringWriter extends StringWriter
{
    public function getStringData(): string
    {
        return gzencode(parent::getStringData());
    }
}
