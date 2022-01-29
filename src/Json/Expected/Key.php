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
class Key extends Enum
{
    public const START = 'key: start';
    public const QUOTED_CHAR = 'key: quoted char';
    public const UNQUOTED_CHAR = 'key: unquoted char';
    public const QUOTED_ENDING = 'key: quoted ending';
    public const UNQUOTED_ENDING = 'key: unquoted ending';

    private const a = 'abcdefghijklmnopqrstuvwxyz';
    private const A = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const d = '0123456789';

    public function allow(): ?string
    {
        switch ($this->value) {
            case self::START:
                return '"\'{[_'.self::a.self::A.self::d;

            case self::QUOTED_CHAR:
                return '*';
            case self::UNQUOTED_CHAR:
                return '_'.self::a.self::A.self::d;

            case self::QUOTED_ENDING:
                return '"\'';
            case self::UNQUOTED_ENDING:
                return ' :';
        }
    }
}
