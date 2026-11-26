<?php

declare(strict_types=1);

namespace Aternos\Nbt\IO\Writer;

use Override;

class StringWriter extends AbstractWriter
{
    protected string $data = "";

    #[Override]
    public function write(string $data): void
    {
        $this->data .= $data;
    }

    #[Override]
    public function tell(): int
    {
        return strlen($this->data);
    }

    public function getStringData(): string
    {
        return $this->data;
    }
}
