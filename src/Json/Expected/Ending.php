<?php

namespace Hyqo\Parser\Json\Expected;

use Hyqo\Enum\Enum;
use Hyqo\Parser\Json\Context;

/**
 * @method static OBJECT_ENDING
 * @method static ARRAY_ENDING
 * @method static FINISH
 */
class Ending extends Enum
{
    public const OBJECT_ENDING = 'ending: object';
    public const ARRAY_ENDING = 'ending: array';
    public const FINISH = 'ending: finish';

    public function allow(): ?string
    {
        switch ($this->value) {
            case self::OBJECT_ENDING:
                return '}';

            case self::ARRAY_ENDING:
                return ']';
        }
    }
}
