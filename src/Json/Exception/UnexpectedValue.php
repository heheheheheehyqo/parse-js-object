<?php

namespace Hyqo\Parser\Json\Exception;

class UnexpectedValue extends \UnexpectedValueException
{
    public function __construct(string $value)
    {
        parent::__construct(sprintf('Unexpected value %s, expected: null, bool, int, float', $value));
    }
}
