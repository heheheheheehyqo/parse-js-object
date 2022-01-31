# parse_js_object( string $string): ?array

![Packagist Version](https://img.shields.io/packagist/v/hyqo/parse-js-object?style=flat-square)
![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/hyqo/parse-js-object?style=flat-square)
![GitHub Workflow Status](https://img.shields.io/github/workflow/status/hyqo/parse-js-object/run-tests?style=flat-square)

Parse string with JS object to PHP object/array

## Install

```sh
composer require hyqo/parse-js-object 
```

## Usage

```php
use function \Hyqo\Parser\parse_js_object;

parse_js_object('{foo: "bar"}');// parse as object 
parse_js_object('{foo: "bar"}', true);// parse as array
```

```text
object(stdClass)#16 (1) {
  ["foo"]=>
  string(3) "bar"
}
```

```text
array(1) {
  ["foo"]=>
  string(3) "bar"
}
```

`bool`, `int`, `float` types or `null` will be parsed respectfully:

| string  | result          |
|---------|-----------------|
| `true`  | bool(true)      |
| `123`   | int(123)        |
| `"123"` | string(3) "123" |
| `1.2`   | float(1.2)      |
| `.2`    | float(0.2)      |

```php
parse_js_object('true'); //bool(true)
parse_js_object('-123'); //int(-123)
```

#### Complex example

```php 
parse_js_object('{string : "foo", "multi_line": "foo\nbar","complex": [{"types": {"int": 123, "true": true, "false": false, "null": null,"array_of_int": [1,2], "empty_array": []}}]}')
```

```text
object(stdClass)#19 (3) {
  ["string"]=>
  string(3) "foo"
  ["multi_line"]=>
  string(7) "foo
bar"
  ["complex"]=>
  array(1) {
    [0]=>
    object(stdClass)#61 (1) {
      ["types"]=>
      object(stdClass)#67 (6) {
        ["int"]=>
        int(123)
        ["true"]=>
        bool(true)
        ["false"]=>
        bool(false)
        ["null"]=>
        NULL
        ["array_of_int"]=>
        array(2) {
          [0]=>
          int(1)
          [1]=>
          int(2)
        }
        ["empty_array"]=>
        array(0) {
        }
      }
    }
  }
}
```
