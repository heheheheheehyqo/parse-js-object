<?php

namespace Hyqo\Parser\Test;

use Hyqo\Parser\Json\UnexpectedToken;
use PHPUnit\Framework\TestCase;

use function Hyqo\Parser\parse_js_object;

class ParseJsObjectTest extends TestCase
{
    public function test_parse_js_object(): void
    {
        $this->assertEquals(
            (object)[
                'foo' => 'bar\\',
                'bar' => [
                    1,
                    (object)[
                        'foo2' => 'bar2',
                        'foo3' => (object)[
                            '' => 666,
                            'foo4' => (object)[],
                        ],
                    ],
                    2,
                    (object)[
                        'f' => ''
                    ]
                ]
            ],
            parse_js_object('{"foo" : "bar\\\\", "bar": [1,{"foo2":"bar2", "foo3": {"":666, "foo4": {}}},2,{"f":""}]}')
        );

        $this->assertEquals(
            (object)[
                'string' => 'foo',
                'multi_line' => "foo\nbar",
                'complex' => [
                    (object)[
                        'types' => (object)[
                            'int' => -123,
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
                ' {string : "foo", \'multi_line\': "foo\nbar","complex": [{"types": {"int": -123, "true": true, "false": false, "null": null,"array_of_int": [1,2], "empty_array": []}}]}'
            )
        );

        $this->assertEquals(
            [
                'foo' => "bar\nbaz",
            ],
            parse_js_object('{"foo": "bar\nbaz"}', true)
        );

        $this->assertEquals(
            [
                'foo' => 0,
                'bar' => [],
            ],
            parse_js_object('{foo : 0, bar: []}', true)
        );

        $this->assertEquals(
            [
                'foo🚧' => [],
            ],
            parse_js_object('{"foo🚧": {}}', true)
        );

        $this->assertEquals(true, parse_js_object('true'));
        $this->assertEquals(1, parse_js_object('1'));
        $this->assertEquals("foo", parse_js_object('"foo"'));
        $this->assertEquals("foo", parse_js_object('\'foo\''));
    }

    public function test_invalid_string()
    {
        foreach (
            [
                '',
                ' {',
                '"{foo: \"bar"}"',
                '"{foo: "bar"}"',
                '{foo: "bar"}}',
                '{"foo: "bar"}}',
                '{foo[',
                '{foo',
                'foo',
            ] as $string
        ) {
            $this->expectException(UnexpectedToken::class);

            parse_js_object($string, false, true);
        }
    }
}
