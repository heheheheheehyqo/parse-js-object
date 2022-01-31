<?php

namespace Hyqo\Parser;

use Hyqo\Parser\Json\UnexpectedToken;

/**
 * @return array|object|null
 */
function parse_js_object(string $json, bool $associative = false, bool $throwOnError = false)
{
    try {
        return JsonParser::doParse(0, $json, $associative)[0] ?? null;
    } catch (UnexpectedToken $e) {
        if ($throwOnError) {
            throw new UnexpectedToken($e->position);
        }

        return null;
    }
}
