<?php

namespace Hyqo\Parser\Test;

use Hyqo\Parser\Json\Exception\UnexpectedToken;
use Hyqo\Parser\JsonParser;
use PHPUnit\Framework\TestCase;

use function Hyqo\Parser\parse_js_object;

class ParseJsObjectTest extends TestCase
{
    public function test_parse_js_object()
    {
//        var_dump((float)'1.1.1');
//        var_dump(preg_quote('\\','/'));
//        var_dump(preg_match(sprintf('/(?<!%1$s)%1$s$/', preg_quote(Sign::BACKSLASH,'/')), 'test\\'));
//        var_dump(substr("", -1));
//        JsonParser::parse('{foo : "bar", "bar": 123, "bool": true, "null":null}');
//        JsonParser::parse('[1,2]');
//        $json1 = json_encode(['^foo' => '&bar\\', 'bar' => [['foo2' => 'bar2'], 2]]);
//        $json =  json_encode(['^foo'=> '&bar\\', 'bar'=>[['foo2'=>'bar2'], 2]],JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
//        $json =  json_encode(['<foo>',"'bar'",'"baz"','&blong&', "\xc3\xa9"],JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

//        var_dump(json_decode($json));

//        var_dump($json);

//        var_dump(json_decode($json3));
//        var_dump(JsonParser::parse($json3, true, 512, JsonParser::VERBOSE));

//        var_dump(JsonParser::parse($json));
//        var_dump(JsonParser::parse($json, true, 512, JsonParser::VERBOSE));

//        var_dump(parse_js_object('"{foo: \"bar\"}"', true, 512, JsonParser::VERBOSE));
//        var_dump(parse_js_object('"{foo: \"bar\"}"', true, 512, JsonParser::VERBOSE));
        $this->assertEquals(
            (object)[
                'string' => 'foo',
                'multi_line' => "foo\nbar",
                'complex' => [
                    (object)[
                        'types' => (object)[
                            'int' => 123,
                            'true' => true,
                            'false' => false,
                            'null' => null,
                            'array_of_int' => [1, 2],
                            'empty_array' => []
                        ],
                    ],
                ]
            ],
            parse_js_object(
                '{string : "foo", "multi_line": "foo\nbar","complex": [{"types": {"int": 123, "true": true, "false": false, "null": null,"array_of_int": [1,2], "empty_array": []}}]}'
            )
        );

        $this->assertEquals(
            [
                'foo' => "bar\nbaz",
            ],
            parse_js_object('{foo : "bar\nbaz",}', true)
        );

        $this->assertEquals(
            [
                'foo🚧' => [],
            ],
            parse_js_object('{"foo🚧": {}}', true)
        );

        $this->assertEquals(null, parse_js_object(''));
        $this->assertEquals(true, parse_js_object('true'));
        $this->assertEquals(1, parse_js_object('1'));
        $this->assertEquals("foo", parse_js_object('"foo"'));
        $this->assertEquals("foo", parse_js_object('\'foo\''));
    }

    public function test_invalid_string()
    {
        foreach (
            [
                '"{foo: \"bar"}"',
                '"{foo: "bar"}"',
                '{foo',
                'foo',
            ] as $string
        ) {
            $this->expectException(UnexpectedToken::class);

            parse_js_object($string);
        }
    }
}
