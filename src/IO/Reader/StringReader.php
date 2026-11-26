<?php

declare(strict_types=1);

namespace Aternos\Nbt\IO\Reader;

use Override;

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

    #[Override]
    public function read(int $length): string
    {
        $length = min($length, $this->length - $this->ptr);
        $oldPtr = $this->ptr;
        $this->ptr += $length;
        return substr($this->data, $oldPtr, $length);
    }

    #[Override]
    public function eof(): bool
    {
        return $this->ptr >= $this->length;
    }

    #[Override]
    public function tell(): int
    {
        return $this->ptr;
    }

    #[Override]
    public function getFormat(): int
    {
        return $this->format;
    }

    #[Override]
    public function returnData(string $data): void
    {
        $this->ptr -= strlen($data);
    }
}
