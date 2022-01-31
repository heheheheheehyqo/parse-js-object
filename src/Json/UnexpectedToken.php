<?php

namespace Hyqo\Parser\Json;

class UnexpectedToken extends \UnexpectedValueException
{
    public $position;

    public function __construct(int $position)
    {
        $this->position = $position;

        parent::__construct(sprintf('Unexpected token at position %d', $position));
    }
}
