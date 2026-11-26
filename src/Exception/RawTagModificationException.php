<?php

declare(strict_types=1);

namespace Aternos\Nbt\Exception;

/**
 * Thrown when a tag that was read raw (see TagOptions) is modified or written in a different format
 */
class RawTagModificationException extends NbtException
{
}
