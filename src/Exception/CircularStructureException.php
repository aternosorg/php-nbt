<?php

declare(strict_types=1);

namespace Aternos\Nbt\Exception;

/**
 * Thrown when a tag contains itself, directly or through its children
 */
class CircularStructureException extends NbtException
{
}
