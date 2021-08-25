<?php

namespace Aternos\Nbt\IO\Writer;

class StringWriter extends AbstractWriter
{
    protected string $data = "";

    /**
     * @inheritDoc
     */
    public function write(string $data): void
    {
        $this->data .= $data;
    }

    /**
     * @inheritDoc
     */
    public function tell(): int
    {
        return strlen($this->data);
    }

    /**
     * @return string
     */
    public function getStringData(): string
    {
        return $this->data;
    }
}
