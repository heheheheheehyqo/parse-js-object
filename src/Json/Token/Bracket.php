<?php

namespace Hyqo\Parser\Json\Token;

use Hyqo\Enum\Enum;

class Bracket extends Enum
{
    private const OPEN_CURLY = '{';
    private const CLOSE_CURLY = '}';
    private const OPEN_SQUARE = '[';
    private const CLOSE_SQUARE = ']';

    public function isOpen(): bool
    {
        switch ($this->value) {
            case self::OPEN_SQUARE:
            case self::OPEN_CURLY:
                return true;
            default:
                return false;
        }
    }

    public function isOpenCurly(): bool
    {
        return $this->value === self::OPEN_CURLY;
    }

    public function isOpenSquare(): bool
    {
        return $this->value === self::OPEN_SQUARE;
    }

    public function isCloseCurly(): bool
    {
        return $this->value === self::CLOSE_CURLY;
    }

    public function isCloseSquare(): bool
    {
        return $this->value === self::CLOSE_SQUARE;
    }

    public function getOpenBracket(): ?string
    {
        switch ($this->value) {
            case self::CLOSE_CURLY:
                return self::OPEN_CURLY;
            case self::CLOSE_SQUARE:
                return self::OPEN_SQUARE;
            default:
                return null;
        }
    }

    public function getCloseBracket(): ?string
    {
        switch ($this->value) {
            case self::OPEN_CURLY:
                return self::CLOSE_CURLY;
            case self::OPEN_SQUARE:
                return self::CLOSE_SQUARE;
            default:
                return null;
        }
    }
}
