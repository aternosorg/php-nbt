<?php

declare(strict_types=1);

namespace Aternos\Nbt\Exception;

/**
 * Thrown when a tag is (indirectly) its own parent, e.g. when writing or resolving its path
 */
class CircularStructureException extends NbtException
{
}
