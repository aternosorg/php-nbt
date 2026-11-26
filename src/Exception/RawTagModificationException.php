<?php

declare(strict_types=1);

namespace Aternos\Nbt\Exception;

/**
 * Thrown when a compound or list tag that was kept as raw bytes (see TagOptions) is modified or written in another format
 */
class RawTagModificationException extends NbtException
{
}
