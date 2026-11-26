<?php

declare(strict_types=1);

namespace Aternos\Nbt\Exception;

/**
 * Thrown when a tag gets a value it cannot hold or serialize, e.g. a wrong array value, an unnamed compound child or a too long string
 */
class InvalidTagValueException extends NbtException
{
}
