<?php

namespace Hyqo\Parser\Json\Expected;

use Hyqo\Enum\Enum;
use Hyqo\Parser\Json\Context;

/**
 * @method static START
 * @method static QUOTED_CHAR
 * @method static UNQUOTED_CHAR
 * @method static QUOTED_ENDING
 * @method static UNQUOTED_ENDING
 */
class Value extends Enum
{
    public const START = 'value: start';
    public const QUOTED_CHAR = 'value: quoted_char';
    public const UNQUOTED_CHAR = 'value: unquoted char';
    public const QUOTED_ENDING = 'value: quoted ending';
    public const UNQUOTED_ENDING = 'value: unquoted ending';

    public function allow(): ?string
    {
        switch ($this->value) {
            case self::START:
                return '"\'{[0123456789.-+trufalsenl';

            case self::QUOTED_CHAR:
                return '*';
            case self::UNQUOTED_CHAR:
                return '0123456789.-+trufalsenl';

            case self::QUOTED_ENDING:
                return '"\'';
            case self::UNQUOTED_ENDING:
                return ' ,]';
        }
    }
}
