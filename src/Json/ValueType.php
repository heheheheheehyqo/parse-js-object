<?php

namespace Hyqo\Parser\Json;

use Hyqo\Enum\Enum;

/**
 * @method static NONE
 * @method static ARRAY
 * @method static STRING
 * @method static PRIMITIVE
 */
class ValueType extends Enum
{
    public const NONE = 'none';
    public const ARRAY = 'array';
    public const STRING = 'string';
    public const PRIMITIVE = 'primitive';
}
