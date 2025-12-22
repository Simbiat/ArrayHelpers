# Array Helpers

Set of useful functions to work with arrays, split into classes based on the type of operation.

## Checkers

Functions to check if something is true or not.

### isMultiDimensional

```php
\Simbiat\Arrays\Checkers::isMultiDimensional(array $array, bool $equal_length = false, bool $all_scalar = false);
```

Checks if an array is multidimensional, essentially if all of its values are arrays. If `$equal_length` is `true`, will also check that all the arrays are of the same length and will throw an error if it's not the case. If `$all_scalar` is `true`, and the array is *not* multidimensional, the function will also check if all values in the array are scalar, and throw an error if at least one of them is not.

### isAssociative

```php
\Simbiat\Arrays\Checkers::isAssociative(array $array);
```

Checks if an array is associative, that is if there is at least one key that's a string (PHP will convert numeric keys to integers by design and will not allow other data types).

### isAllScalar

```php
\Simbiat\Arrays\Checkers::isAllScalar(array $array);
```

Checks if an array consists only of scalar values or not.

## Converters

Functions to convert something to something else.

### multiToSingle

```php
\Simbiat\Arrays\Converters::multiToSingle(array $old_array, string $key_to_save);
```

Converts a multidimensional array to "flat" (or "flatter") one. Essentially a `array_column`, but also uses `array_combine` and `array_keys` to preserve the keys.

### dbfToArray

```php
\Simbiat\Arrays\Converters::dbfToArray(string $file);
```

Converts contents of a [DBF](https://en.wikipedia.org/wiki/.dbf) file to an array.

### attributesToArray

```php
\Simbiat\Arrays\Converters::attributesToArray(\DOMNode|Node $node, bool $null = true, array $extra_attributes = []);
```

Converts `\DOMNode` or `\Dom\Node` into an array with a set of attributes, present in the node, as the array's keys. If `$null` is set to `true` empty attributes will be replaced with `null` (instead of empty string). You can also pass a list of attributes in `$extra_attributes`, which will be added to all resulting elements, if they do not have that attribute. Extra attributes are added as either an empty string or a `null` if `$null` is `true`.

### toMultiArray

```php
\Simbiat\Arrays\Converters::toMultiArray(array $array, array $keys);
```

Turns a regular array to a multidimensional one by turning provided keys into one of the columns. It will turn an array like

```php
[
    'apple' => 1,
    'banana' => 2,
]
```

To an array like

```php
[
    [
        'name' => 'apple',
        'value' => 1
    ],
    [
        'name' => 'banana',
        'value' => 2
    ],
]
```

if you provide the below array as `$keys`:

```php
[
    'name',
    'value',
]
```

### arrayToProperties

```php
\Simbiat\Arrays\Converters::arrayToProperties(object $object, array $array, array $skip = [], bool $strict = true);
```

"Converts" an array to an object's properties (just sets their values to those from the array). Useful when you need to populate them based on results from some function, for example, a `SELECT` from database. Only values with string keys will be processed.

If `$skip` array is passed to the function and a key from `$array` is present there, it will be skipped. If `$strict` is set to `true`, and a property does not exist in the object, an exception will be thrown.

### enumValues

```php
\Simbiat\Arrays\Converters::enumValues(string $enum);
```

Gets either list of cases' values from a backed enum. `$enum` is expected to be something like `\Path\To\Enum::class`.

### enumNames

```php
\Simbiat\Arrays\Converters::enumNames(string $enum);
```

Gets either list of cases' names an enum. `$enum` is expected to be something like `\Path\To\Enum::class`.

## Editors

Functions that somehow edit the array content.

### digitToKey

```php
\Simbiat\Arrays\Editors::digitToKey(array $old_array, string $new_key, bool $key_unset = false);
```

Replaces a multidimensional array's values with values from a specific column. With PHP updates this has become a wrapper for `array_column`, but `$key_unset` set to `true` allows you to remove the chose column after rekeying.

### ColumnsConversion

```php
\Simbiat\Arrays\Editors::columnsConversion(array $array, array|string $columns, string $type = 'int');
```

Allows casting values in a column (or set of columns) to a specific type. Supported values for `$type` are: `int`/`integer`, `bool`/`boolean`, `float`/`double`/`real`, `string`, `array`, `object`. Casting is done by native functions.

### RemoveByValue

```php
\Simbiat\Arrays\Editors::removeByValue(array $array, mixed $remove_value, bool $rekey = false);
```

Simple function that removes all elements with a certain value (`$remove_value`) and optionally re-keys it if `$rekey` is `true (useful for an indexed array, useless for associative ones).

### setKeyPath

```php
\Simbiat\Arrays\Editors::setKeyPath(array $array, array $path, mixed $value);
```

Allows recursively setting a key path based on logic from [StackOverflow](https://stackoverflow.com/a/5821027/2992851). Useful for "generating" arrays of a specific shape or updating existing ones.  
`$path` is an array where each key is part of a new path with format like this:

```php
['new', 'path']
```

It is meant to be "converted" and result in

```php
$array['new']['path']);
```

Parts of the path are created only if they are not present already.  
`$value` is the value that will be assigned to the newly created path. `$array` is passed by reference.

### moveToSubarray

```php
\Simbiat\Arrays\Editors::moveToSubarray(array $array, string|int $key, array $new_key_path);
```

Function to move keys into a subarray. For example, you have a key like `$array['key']`, but you want to remove it and have it as `$array['subarray']['key']` - then use this function. Purely for data formatting.  
`$new_key_path` requires the same format as `$path` in `setKeyPath()`. `$array` is passed by reference.

### renameColumn

```php
\Simbiat\Arrays\Editors::renameColumn(array $array, string $column, string $key_name);
```

Rename a column in a multidimensional array. `$array` is passed by reference.

## Sorters

Functions to sort arrays (just one for now).

### multiArrSort

```php
\Simbiat\Arrays\Sorters::multiArrSort(array $array, string $column, bool $desc = false);
```

Function to sort a multidimensional array by values in a column. Can be "reversed" to sort from larger to smaller (descending order), if `$desc` is set to `true`.

## Splitters

Functions to split arrays into parts.

### topAndBottom

```php
\Simbiat\Arrays\Splitters::topAndBottom(array $array, int $rows = 0);
```

Function that splits the array to 2 representing first X and last X rows from it, providing a way to get "Top X" and its counterpart. If `$rows` is less than `1` or the array size is less than `$rows * 2`, then function will try to split the array evenly. Resulting array will have `top` and `bottom` keys with respective rows, but if the array has only one element, an exception will be thrown.

### splitByKey

```php
\Simbiat\Arrays\Splitters::splitByKey(array $array, string $column_key, array $new_keys = [], bool $keep_key = false, bool $case_insensitive = false);
```

Splits a multidimensional array by values from a column. Useful to reduce the number of travels to a database. Instead of doing 2+ queries separately, we do just one query and then split the results to several arrays in code. Turns an array like this:

```php
[
    [
        'type' => 'int',
        'value' => 1,
    ],
    [
        'type' => 'int',
        'value' => 2,
    ],
    [
        'type' => 'string',
        'value' => '1',
    ],
    [
        'type' => 'string',
        'value' => '1',
    ],
]
```

to this:

```php
[
    'int' => [
        ['value' => 1],
        ['value' => 2],
    ],
    'string' => [
        ['value' => '1'],
        ['value' => '2'],
    ]
]
```

If `$keep_key` is set to `true` will retain the original column in all the rows. If `$case_insensitive` is `true` will do `mb_strtolower()` on all the keys from the column to ensure correct splitting (that's the case where you *may* want to retain the original value of the key, too).  
If `$new_keys` is empty (default), then `array_column` will be used to get the keys for the resulting array. However, if a list of integers or strings (or mix, which is not recommended) is provided, it will act as a filter, discarding rows, which have the column's value, that's not in the list.