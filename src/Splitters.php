<?php
declare(strict_types = 1);

namespace Simbiat\Arrays;

use function array_slice, count, is_string;

/**
 * Functions that split arrays
 */
class Splitters
{
    /**
     * Function that splits the array to 2 representing first X and last X rows from it, providing a way to get 'Top X' and its counterpart
     * @param array $array Array to process.
     * @param int   $rows  Number of rows to select (from top and bottom separately). Send `0` or negative number to split evenly.
     *
     * @return array
     */
    public static function topAndBottom(array $array, int $rows = 0): array
    {
        if (empty($array)) {
            return ['top' => [], 'bottom' => []];
        }
        if (count($array) === 1) {
            throw new \UnexpectedValueException('Array provided to `topAndBottom` function contains only 1 element.');
        }
        #If the number of rows sent is <=0 or the number of elements is lower than the number of rows x2, attempt to split evenly
        if ($rows <= 0 || count($array) < ($rows * 2)) {
            $rows = (int)\floor(count($array) / 2);
        }
        $new_array['top'] = array_slice($array, 0, $rows);
        $new_array['bottom'] = \array_reverse(array_slice($array, -$rows, $rows));
        return $new_array;
    }
    
    /**
     * Useful to reduce the number of travels to a database. Instead of doing 2+ queries separately, we do just 1 query and then split the results to several arrays in code.
     * If required, you can send a list of keys that you expect, which can work as a filter.
     *
     * @param array  $array            Array to process.
     * @param string $column_key       Column key to split by.
     * @param array  $new_keys         Optional list of expected new keys (that is values from the column). Can be used to essentially filter results. If empty, unique key values from the array will be used.
     * @param bool   $keep_key         Whether to retain the original key in the resulting array or not
     * @param bool   $case_insensitive Whether to do case-sensitive comparison of the values or not.
     *
     * @return array
     */
    public static function splitByKey(array $array, string $column_key, array $new_keys = [], bool $keep_key = false, bool $case_insensitive = false): array
    {
        #Predefine the empty array
        $new_array = [];
        #Checking values
        if (empty($array)) {
            return [];
        }
        if (empty($column_key)) {
            throw new \InvalidArgumentException('Empty key provided to splitByKey function.');
        }
        if (empty($new_keys)) {
            $new_keys = \array_unique(\array_column($array, $column_key));
            \asort($new_keys, \SORT_NATURAL);
        }
        #If we use case-insensitive comparison, we need to ensure standardized keys and lack of duplicates
        if ($case_insensitive) {
            foreach ($new_keys as $key => $value) {
                if (!\is_numeric($value)) {
                    $new_keys[$key] = mb_strtolower($value, 'UTF-8');
                }
            }
            $new_keys = \array_unique($new_keys, \SORT_NATURAL);
        }
        #Prepare an empty array
        foreach ($new_keys as $arr_key => $new_key) {
            if (empty($new_key)) {
                throw new \UnexpectedValueException('New key with index value \''.$arr_key.'\' is empty and cannot be used as key for new array by splitByKey function.');
            }
            if (\is_int($new_key)) {
                $new_array[(string)$new_key] = [];
            } elseif (is_string($new_key)) {
                if ($case_insensitive) {
                    $new_array[mb_strtolower($new_key, 'UTF-8')] = [];
                } else {
                    $new_array[$new_key] = [];
                }
            } else {
                throw new \UnexpectedValueException('New key with index value \''.$arr_key.'\' is neither string nor integer and cannot be used as key for new array by splitByKey function.');
            }
        }
        foreach ($new_array as $key => $value) {
            foreach ($array as $item) {
                #Standardize keys, in case we are using case-insensitive comparison
                if ($case_insensitive && is_string($item[$column_key]) && is_string($key)) {
                    $key_to_compare = mb_strtolower($item[$column_key], 'UTF-8');
                } else {
                    $key_to_compare = (string)$item[$column_key];
                }
                #Compare values. Need to force $key to be a string, because if a *value* was an integer, PHP will automatically treat the key as one, and not as a string with numeric values
                if ($key_to_compare === (string)$key) {
                    #Remove the column key, since it's not required after this
                    if (!$keep_key) {
                        unset($item[$column_key]);
                    }
                    $new_array[$key][] = $item;
                }
            }
        }
        return $new_array;
    }
}