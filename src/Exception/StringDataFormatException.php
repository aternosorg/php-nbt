<?php

declare(strict_types=1);

namespace Aternos\Nbt\Exception;

/**
 * Thrown when a string is not valid in its encoding (modified UTF-8 in NBT data, or the source encoding when writing) or shorter than its length prefix
 */
class StringDataFormatException extends NbtException
{
}
