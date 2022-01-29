<?php

namespace Hyqo\Parser\Json;

use Hyqo\Enum\Enum;

/**
 * @method static VALUE_START
 * @method static QUOTED_VALUE_CHAR
 * @method static UNQUOTED_VALUE_CHAR
 * @method static QUOTED_VALUE_END
 * @method static UNQUOTED_VALUE_END
 *
 * @method static KEY_START
 * @method static QUOTED_KEY_CHAR
 * @method static QUOTED_KEY_END
 * @method static UNQUOTED_KEY_CHAR
 * @method static UNQUOTED_KEY_END
 *
 * @method static COLON
 *
 * @method static END
 * @method static NOTHING
 * @method static DIVE
 */
class Expect extends Enum
{
    public const VALUE_START = 1 << 0;
    public const QUOTED_VALUE_CHAR = 1 << 2;
    public const UNQUOTED_VALUE_CHAR = 1 << 3;
    public const QUOTED_VALUE_END = 1 << 4;
    public const UNQUOTED_VALUE_END = 1 << 5;

    public const KEY_START = 1 << 10;
    public const QUOTED_KEY_CHAR = 1 << 11;
    public const QUOTED_KEY_END = 1 << 12;
    public const UNQUOTED_KEY_CHAR = 1 << 13;
    public const UNQUOTED_KEY_END = 1 << 14;

    public const COLON = 1 << 20;

    public const OBJECT_END = 1 << 30;
    public const ARRAY_END = 1 << 31;
    public const STRING_END = 1 << 32;
    public const PRIMITIVE_END = 1 << 33;

    public function regex(): ?string
    {
        switch ($this->value) {
            case self::VALUE_START:
                return '/["\'{\[0-9]/';
            case self::UNQUOTED_VALUE_CHAR:
                return '/[0-9.]/';
            case self::UNQUOTED_VALUE_END:
                return '/[,\]\}]/';

            case self::KEY_START:
                return '/["\'\w]/';
            case self::UNQUOTED_KEY_CHAR:
                return '/[\w, :]/';
            case self::UNQUOTED_KEY_END:
                return '/[ :]/';

            case self::QUOTED_VALUE_END:
            case self::QUOTED_KEY_END:
                return '/["\']/';

            case self::QUOTED_KEY_CHAR:
            case self::QUOTED_VALUE_CHAR:
                return '/./';

            case self::COLON:
                return '/:/';

            case self::OBJECT_END:
                return '/[},]/';
            case self::ARRAY_END:
                return '/[\],]/';

            default:
                return null;
        }
    }

    public function backwardRegex(): ?string
    {
        switch ($this->value) {
            case self::QUOTED_KEY_END:
            case self::QUOTED_VALUE_END:
                return sprintf('/(?<!%1$s)%1$s$/', preg_quote('\\', '/'));
            default:
                return null;
        }
    }
}
