<?php

namespace Hyqo\Parser\Json\Expected;

use Hyqo\Enum\Enum;
use Hyqo\Parser\Json\Context;

/**
 * @method static COLON
 * @method static COMMA
 */
class Sign extends Enum
{
    public const COLON = 'sign: colon';
    public const COMMA = 'sign: comma';

    public function allow(): ?string
    {
        switch ($this->value) {
            case self::COLON:
                return ':';
            case self::COMMA:
                return ',';
        }
    }
}
