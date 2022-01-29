<?php

namespace Hyqo\Parser;

/**
 * @return array|object
 */
function parse_js_object(string $json, bool $associative = false, int $depth = 512, int $flags = 0)
{
//        if (null !== $data = json_decode($json, $associative, $depth)) {
//            return $data;
//        }

    return JsonParser::doParse(0, $json, $associative, $flags)[0] ?? null;
}
