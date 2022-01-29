<?php

namespace Hyqo\Parser;

use Hyqo\Parser\Json\Context;
use Hyqo\Parser\Json\Exception\UnexpectedToken;
use Hyqo\Parser\Json\Exception\UnexpectedValue;
use Hyqo\Parser\Json\Expected\Ending;
use Hyqo\Parser\Json\Expected\Key;
use Hyqo\Parser\Json\Expected\Sign;
use Hyqo\Parser\Json\Expected\Value;
use Hyqo\Parser\Json\Token\Bracket;
use Hyqo\Parser\Json\Token\Quote;
use Hyqo\Parser\Json\ValueType;

abstract class JsonParser
{
    public const VERBOSE = 1 << 0;

    public static function doParse(int $startAt, string $string, bool $associative, int $flags): array
    {
        if (!$string) {
            return [null, $startAt];
        }

        $verbose = $flags & self::VERBOSE;

        $verbose && self::log(sprintf("parse: %s start at %d\n", $string, $startAt));

        $data = null;
        $tokens = new \SplStack();

        $position = $startAt;
        $length = strlen($string);

//        $expect = Expect::VALUE_START();
        $expected = [Value::START()];
        $context = Context::NONE();
        $valueType = ValueType::NONE();

        $buffer = null;

        do {
            $char = $string[$position];

            if ($buffer === null && preg_match('/\s/', $char)) {
                continue;
            }

            $verbose && self::log(
                sprintf(
                    "allow (%s)\n",
                    implode(
                        ', ',
                        array_map(function ($expect) {
                            return $expect->value;
                        }, $expected)
                    )
                )
            );

            $passed = false;
            foreach ($expected as $expect) {
                $verbose && self::log(sprintf("%s — %s", $char, $expect->value));

                $allow = $expect->allow();

                if ($allow === '*' || strpos($allow, $char) !== false) {
                    $verbose && self::log(" — PASSED\n");
                    $passed = true;
                    break;
                }

                $verbose && self::log(" — FAIL\n");
            }

            if (!$passed) {
                throw new UnexpectedToken($char, $position);
            }

            if ($expect->value === Value::START) {
                if ($bracket = Bracket::tryFrom($char)) {
                    switch (true) {
                        case $data !== null:
                            $verbose && self::log("look inside\n");

                            [$value, $skip] = self::doParse($position, $string, $associative, $flags);
                            $valueType = ValueType::ARRAY();
                            $verbose && self::log(sprintf("value type: %s\n", $valueType->name));

                            self::writeValue($context, $data, $value, $valueType);

                            $position += $skip - 1;
                            $expected = [Sign::COMMA()];

                            if ($context->value === Context::OBJECT) {
                                $expected[] = Ending::OBJECT_ENDING();
                            }

                            if ($context->value === Context::ARRAY) {
                                $expected[] = Ending::ARRAY_ENDING();
                            }

                            $verbose && self::log(sprintf("continue from %s, skipped: %d\n", $position, $skip));
                            continue 2;
                        case $bracket->isOpenSquare():
                            $context = Context::ARRAY();
                            $expected = [Value::START(), Ending::ARRAY_ENDING()];
                            break;
                        case $bracket->isOpenCurly():
                            $context = Context::OBJECT();
                            $expected = [Key::START(), Ending::OBJECT_ENDING()];
                            break;
                    }

                    $data = [];
                    $tokens->push($char);

                    $verbose && self::log(sprintf("switch context: %s\n", $context->name));
                    continue;
                }

                if ($quote = Quote::tryFrom($char)) {
                    $valueType = ValueType::STRING();
                    $verbose && self::log(sprintf("value type: %s\n", $valueType->name));

                    $expected = [Value::QUOTED_CHAR()];
                    $tokens->push($char);
                    $buffer = '';

                    continue;
                }

                $valueType = ValueType::PRIMITIVE();
                $verbose && self::log(sprintf("value type: %s\n", $valueType->name));

                $expected = [Value::UNQUOTED_CHAR()];

                if ($context->value === Context::OBJECT) {
                    $expected[] = Sign::COMMA();
                    $expected[] = Ending::OBJECT_ENDING();
                }

                if ($context->value === Context::ARRAY) {
                    $expected[] = Sign::COMMA();
                    $expected[] = Ending::ARRAY_ENDING();
                }

                $buffer = $char;
            }

            if ($expect->value === Key::START) {
                if ($quote = Quote::tryFrom($char)) {
                    $expected = [Key::QUOTED_CHAR()];
                    $tokens->push($char);
                    $buffer = '';
                    continue;
                }

                $expected = [Key::UNQUOTED_CHAR(), Sign::COLON()];
                $buffer = $char;
                continue;
            }

            if ($expect->value === Key::UNQUOTED_CHAR) {
                $expected = [Key::UNQUOTED_CHAR(), Key::UNQUOTED_ENDING()];
                $buffer .= $char;
                continue;
            }

            if ($expect->value === Key::UNQUOTED_ENDING) {
                $verbose && self::log(sprintf("\ncomplete unquoted key: %s\n\n", $buffer));
                $data[$buffer] = null;

                if (Sign::COLON()->allow() === $char) {
                    $expected = [Value::START()];
                } else {
                    $expected = [Sign::COLON()];
                }

                $buffer = null;
                continue;
            }

            if ($expect->value === Key::QUOTED_CHAR) {
                if (
                    $char === $tokens->top()
                    && self::isValidQuote($buffer)
                    && strpos(Key::QUOTED_ENDING()->allow(), $char) !== false
                ) {
                    $verbose && self::log(sprintf("\ncomplete quoted key: %s\n\n", $buffer));
                    $data[$buffer] = null;

                    $expected = [Sign::COLON()];
                    $tokens->pop();
                    $buffer = null;
                    continue;
                }

                $buffer .= $char;
                continue;
            }

            if ($expect->value === Sign::COLON) {
                if ($buffer !== null) {
                    $data[$buffer] = null;
                }
                $expected = [Value::START()];
                continue;
            }

            if ($expect->value === Value::UNQUOTED_CHAR) {
                $expected = [Value::UNQUOTED_CHAR(), Value::UNQUOTED_ENDING()];

                if ($context->value === Context::OBJECT) {
                    $expected[] = Ending::OBJECT_ENDING();
                } elseif ($context->value === Context::ARRAY) {
                    $expected[] = Ending::ARRAY_ENDING();
                }

                $buffer .= $char;
                continue;
            }

            if ($expect->value === Value::UNQUOTED_ENDING) {
                $verbose && self::log(sprintf("\ncomplete unquoted value: %s\n\n", $buffer));

                self::writeValue($context, $data, $buffer, $valueType);

                if (Sign::COMMA()->allow() === $char) {
                    $expected = [Key::START()];
                } else {
                    $expected = [Sign::COMMA()];

                    if ($context->value === Context::OBJECT) {
                        $expected[] = Ending::OBJECT_ENDING();
                    } elseif ($context->value === Context::ARRAY) {
                        $expected[] = Ending::ARRAY_ENDING();
                    }
                }

                continue;
            }

            if ($expect->value === Value::QUOTED_CHAR) {
                if (!$tokens->count()) {
                    throw new UnexpectedToken($char, $position);
                }

                if (
                    $char === $tokens->top()
                    && self::isValidQuote($buffer)
                    && strpos(Value::QUOTED_ENDING()->allow(), $char) !== false
                ) {
                    $verbose && self::log(sprintf("\ncomplete quoted value: %s\n\n", $buffer));

                    self::writeValue($context, $data, $buffer, $valueType);

                    if ($context->value === Context::OBJECT) {
                        $expected = [Sign::COMMA(), Ending::OBJECT_ENDING()];
                    }

                    if ($context->value === Context::ARRAY) {
                        $expected = [Sign::COMMA(), Ending::ARRAY_ENDING()];
                    }

                    $tokens->pop();
                    continue;
                }

                $buffer .= $char;
                $verbose && self::log(sprintf("buffer: %s\n", $buffer));
                continue;
            }

            if ($expect->value === Sign::COMMA) {
                self::writeValue($context, $data, $buffer, $valueType);

                if ($context->value === Context::OBJECT) {
                    $expected = [Key::START(), Ending::OBJECT_ENDING()];
                } elseif ($context->value === Context::ARRAY) {
                    $expected = [Value::START(), Ending::ARRAY_ENDING()];
                }
                continue;
            }

            if (
                $expect->value === Ending::OBJECT_ENDING ||
                $expect->value === Ending::ARRAY_ENDING
            ) {
                self::writeValue($context, $data, $buffer, $valueType);
                $tokens->pop();
                break;
            }
        } while (++$position < $length);

        if (!$associative && $context->value === Context::OBJECT) {
            $data = (object)$data;
        }

        if ($buffer && $context->value === Context::NONE) {
            self::writeValue($context, $data, $buffer, $valueType);
        }

        $verbose && self::log("------------------end level with result:\n");
        $verbose && var_dump($buffer);
        $verbose && var_dump($data);

//        if ($stack->count()) {
//            return [];
//        }


        return [$data, $position - $startAt + 1];
    }

    private static function writeValue(Context $context, &$data, &$buffer, ValueType $valueType)
    {
        if ($buffer === null) {
            return;
        }

        if ($valueType->value === ValueType::PRIMITIVE) {
            switch (true) {
                case preg_match('/^null$/i', $buffer):
                    $value = null;
                    break;
                case preg_match('/^(?:true|false)$/i', $buffer):
                    $value = filter_var($buffer, FILTER_VALIDATE_BOOLEAN);
                    break;
                case preg_match('/^[\d]+$/', $buffer):
                    $value = (int)$buffer;
                    break;
                case preg_match('/^[-+]?[\d]*.[\d]+$/', $buffer):
                    $value = (float)$buffer;
                    break;
                default:
                    throw new UnexpectedValue($buffer);
            }
        } elseif ($valueType->value === ValueType::STRING) {
            $value = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/i', function ($escaped) {
                return chr(hexdec($escaped[1]));
            }, $buffer);
            $value = stripcslashes($value);
        } elseif ($valueType->value === ValueType::ARRAY) {
            $value = $buffer;
        } else {
            return;
        }

        if ($context->value === Context::OBJECT) {
            end($data);
            $data[key($data)] = $value;
        } elseif ($context->value === Context::ARRAY) {
            if ($data === null) {
                $data = [];
            }

            $data[] = $value;
        } else {
            $data = $value;
        }

        $buffer = null;
    }

    private static function isValidQuote($buffer): bool
    {
        return !preg_match('/(?<!\\\\)(?:\\\\\\\\\\\\)+$|(?<!\\\\)\\\\$/', $buffer);
    }

    private static function log(string $message): void
    {
        echo $message;
    }
}
