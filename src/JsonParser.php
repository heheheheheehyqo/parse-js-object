<?php

namespace Hyqo\Parser;

use Hyqo\Parser\Json\UnexpectedToken;

abstract class JsonParser
{
    public static function doParse(int $startAt, string $string, bool $associative): array
    {
        $result = self::parseValue($startAt, $string, $associative);

        if(trim(substr($string, $result[1]))){
            throw new UnexpectedToken($result[1]);
        }

        return $result;
    }

    private static function parseValue(int $startAt, string $string, bool $associative): array
    {
        $position = $startAt;

        if (preg_match(
            '/^\s*(?:(?:(?P<bool>true|false)|(?P<null>null)|(?P<float>[-+]?\d*\.\d+)|(?P<int>[-+]?\d+))(?=[,}\]\s]|$)|(?P<start_object>{)|(?P<start_array>\[)|(?P<start_string>["\']))/',
            substr($string, $position),
            $matches
        )) {
            if ($matches['null'] ?? '') {
                return [null, $position + strlen($matches[0])];
            }

            if ($value = $matches['bool'] ?? '') {
                return [filter_var($value, FILTER_VALIDATE_BOOLEAN), $position + strlen($matches[0])];
            }

            if ('' !== ($value = $matches['int'] ?? '')) {
                return [(int)$value, $position + strlen($matches[0])];
            }

            if ($value = $matches['float'] ?? '') {
                return [(float)$value, $position + strlen($matches[0])];
            }

            if ($quote = $matches['start_string'] ?? '') {
                $value = substr($string, $position + strlen($matches[0]));

                if (preg_match(
                    "/^(?P<value>(?:(?:\\\\\\\\)*\\\\$quote|\\\\[^$quote]|[^$quote\\\\])*)$quote/",
                    $value,
                    $valueMatch
                )) {
                    $value = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/i', static function ($escaped) {
                        return chr(hexdec($escaped[1]));
                    }, $valueMatch['value']);
                    $value = stripcslashes($value);

                    return [
                        $value,
                        $position + strlen($matches[0]) + strlen($valueMatch[0])
                    ];
                }
            }

            if ($matches['start_object'] ?? '') {
                return self::parseObject($position + strlen($matches[0]) - 1, $string, $associative);
            }

            if ($matches['start_array'] ?? '') {
                return self::parseArray($position + strlen($matches[0]) - 1, $string, $associative);
            }
        }

        throw new UnexpectedToken($position);
    }

    private static function parseObject(int $startAt, string $string, bool $associative): array
    {
        $object = [];
        $position = $startAt + 1;

        while (true) {
            if (preg_match(
                '/^\s*(?:"(?P<double_quoted>(?:(?:\\\\\\\\)*\\\\"|\\\\[^"]|[^"\\\\])*)"|\'(?P<single_quoted>(?:(?:\\\\\\\\)*\\\\\'|\\\\[^\']|[^\'\\\\])*)\'|(?P<unquoted>[\w]+))\s*:/',
                substr($string, $position),
                $matches
            )) {
                $key = $matches['unquoted'] ?? $matches['single_quoted'] ?? $matches['double_quoted'];
                $position += strlen($matches[0]);

                [$value, $position] = self::parseValue($position, $string, $associative);

                $object[$key] = $value;
            }

            if (preg_match('/^\s*,/', substr($string, $position), $matches)) {
                $position += strlen($matches[0]);
            } elseif (preg_match('/^\s*}/', substr($string, $position), $matches)) {
                $position += strlen($matches[0]);
                break;
            } else {
                throw new UnexpectedToken($position);
            }
        }

        return [$associative ? $object : (object)$object, $position];
    }

    private static function parseArray(int $startAt, string $string, $associative): array
    {
        $array = [];
        $position = $startAt + 1;

        while (true) {
            if (preg_match('/^\s*[^]]/', substr($string, $position), $matches)) {
                [$value, $position] = self::parseValue($position, $string, $associative);
                $array[] = $value;
            }

            if (preg_match('/^\s*,/', substr($string, $position), $matches)) {
                $position += strlen($matches[0]);
            } elseif (preg_match('/^\s*]/', substr($string, $position), $matches)) {
                $position += strlen($matches[0]);
                break;
            } else {
                throw new UnexpectedToken($position);
            }
        }

        return [$array, $position];
    }
}
