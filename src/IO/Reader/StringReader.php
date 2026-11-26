<?php

declare(strict_types=1);

namespace Aternos\Nbt\IO\Reader;

use Aternos\Nbt\NbtFormat;
use Override;

class StringReader extends AbstractReader
{
    protected int $pointer = 0;
    protected readonly int $length;

    public function __construct(protected readonly string $data, NbtFormat $format)
    {
        $this->format = $format;
        $this->length = strlen($data);
    }

    #[Override]
    public function read(int $length): string
    {
        $length = min($length, $this->length - $this->pointer);
        $start = $this->pointer;
        $this->pointer += $length;
        return substr($this->data, $start, $length);
    }

    #[Override]
    public function eof(): bool
    {
        return $this->pointer >= $this->length;
    }

    #[Override]
    public function tell(): int
    {
        return $this->pointer;
    }

    #[Override]
    public function returnData(string $data): void
    {
        $this->pointer -= strlen($data);
    }
}
