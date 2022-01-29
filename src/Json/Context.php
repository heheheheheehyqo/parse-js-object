<?php

namespace Hyqo\Parser\Json;

use Hyqo\Enum\Enum;

/**
 * @method static NONE
 * @method static OBJECT
 * @method static ARRAY
 */
class Context extends Enum
{
    public const NONE = 'none';
    public const OBJECT = 'object';
    public const ARRAY = 'array';
}
