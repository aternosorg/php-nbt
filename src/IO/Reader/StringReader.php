<?php

namespace Aternos\Nbt\IO\Reader;

class StringReader extends AbstractReader
{
    protected string $data;
    protected int $ptr = 0;
    protected int $length;

    public function __construct(string $data, int $format)
    {
        $this->format = $format;
        $this->data = $data;
        $this->length = strlen($data);
    }

    /**
     * @inheritDoc
     */
    public function read(int $length): string
    {
        $length = min($length, $this->length - $this->ptr);
        $oldPtr = $this->ptr;
        $this->ptr += $length;
        return substr($this->data, $oldPtr, $length);
    }

    /**
     * @inheritDoc
     */
    public function eof(): bool
    {
        return $this->ptr >= $this->length;
    }

    /**
     * @inheritDoc
     */
    public function tell(): int
    {
        return $this->ptr;
    }

    /**
     * @inheritDoc
     */
    public function getFormat(): int
    {
        return $this->format;
    }

    /**
     * @inheritDoc
     */
    public function returnData(string $data): void
    {
        $this->ptr -= strlen($data);
    }
}
