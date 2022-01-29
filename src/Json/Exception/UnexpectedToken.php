<?php

namespace Hyqo\Parser\Json\Exception;

class UnexpectedToken extends \UnexpectedValueException
{
    public function __construct(string $char, int $position)
    {
        parent::__construct(sprintf('Unexpected token %s at position %d', $char, $position));
    }
}
