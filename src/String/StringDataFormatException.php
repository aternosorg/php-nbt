<?php

declare(strict_types=1);

namespace Aternos\Nbt\String;

use Aternos\Nbt\Exception\StringDataFormatException as BaseStringDataFormatException;

/**
 * Still thrown by this library, so existing catch blocks for this class keep working
 *
 * @deprecated catch \Aternos\Nbt\Exception\StringDataFormatException instead, this class will be removed in the next major version
 */
class StringDataFormatException extends BaseStringDataFormatException
{
}
