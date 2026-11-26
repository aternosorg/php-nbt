<?php

declare(strict_types=1);

namespace Aternos\Nbt\Exception;

/**
 * Thrown when a string in NBT data is not valid (modified) UTF-8 or shorter than its length prefix
 */
class StringDataFormatException extends NbtException
{
}
