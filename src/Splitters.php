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
     * @param array $array Array to process
     * @param int   $rows  Number of rows to select (from top and bottom separately)
     *
     * @return array
     */
    public static function topAndBottom(array $array, int $rows = 0): array
    {
        if (empty($array)) {
            throw new \InvalidArgumentException('Empty array provided to topAndBottom function.');
        }
        if (count($array) === 1) {
            throw new \UnexpectedValueException('Array provided to topAndBottom function contains only 1 element.');
        }
        #If number of rows sent is <=0 or the amount of elements is lower than the number of rows x2, attempt to split evenly
        if ($rows <= 0 || count($array) < ($rows * 2)) {
            $rows = (int)floor(count($array) / 2);
        }
        $newArray['top'] = array_slice($array, 0, $rows);
        $newArray['bottom'] = array_reverse(array_slice($array, -$rows, $rows));
        return $newArray;
    }
    
    /**
     * Useful to reduce number of travels to database. Instead of doing 2+ queries separately, we do just 1 query and then split it to several arrays in code.
     * If required you can send list of keys, that you expect, which can work as a filter.
     *
     * @param array  $array           Array to process.
     * @param string $columnKey       Column key to split by.
     * @param array  $newKeys         Optional list of expected new keys (that is values from the column). Can be used to essentially filter results. If empty unique key values from the array will be used.
     * @param bool   $keepKey         Whether to retain the original key in the resulting array or not
     * @param bool   $caseInsensitive Whether to do case-sensitive comparison of the values or not.
     *
     * @return array
     */
    public static function splitByKey(array $array, string $columnKey, array $newKeys = [], bool $keepKey = false, bool $caseInsensitive = false): array
    {
        #Predefine empty array
        $newArray = [];
        #Checking values
        if (empty($array)) {
            throw new \InvalidArgumentException('Empty array provided to splitByKey function.');
        }
        if (empty($columnKey)) {
            throw new \InvalidArgumentException('Empty key provided to splitByKey function.');
        }
        if (empty($newKeys)) {
            $newKeys = array_unique(array_column($array, $columnKey));
            asort($newKeys, SORT_NATURAL);
        }
        #If we use case-insensitive comparison, we need to ensure standardized keys and lack of duplicates
        if ($caseInsensitive) {
            foreach ($newKeys as $key => $value) {
                if (!is_numeric($value)) {
                    $newKeys[$key] = mb_strtolower($value, 'UTF-8');
                }
            }
            $newKeys = array_unique($newKeys, SORT_NATURAL);
        }
        #Prepare empty array
        foreach ($newKeys as $arrKey => $newKey) {
            if (empty($newKey)) {
                throw new \UnexpectedValueException('New key with index value \''.$arrKey.'\' is empty and cannot be used as key for new array by splitByKey function.');
            }
            if (\is_int($newKey)) {
                $newArray[(string)$newKey] = [];
            } elseif (is_string($newKey)) {
                if ($caseInsensitive) {
                    $newArray[mb_strtolower($newKey, 'UTF-8')] = [];
                } else {
                    $newArray[$newKey] = [];
                }
            } else {
                throw new \UnexpectedValueException('New key with index value \''.$arrKey.'\' is neither string nor integer and cannot be used as key for new array by splitByKey function.');
            }
        }
        foreach ($newArray as $key => $value) {
            foreach ($array as $item) {
                #Standardize keys, in case we are using case-insensitive comparison
                if ($caseInsensitive && is_string($item[$columnKey]) && is_string($key)) {
                    $keyToCompare = mb_strtolower($item[$columnKey], 'UTF-8');
                } else {
                    $keyToCompare = (string)$item[$columnKey];
                }
                #Compare values. Need to force $key to be a string, because if a *value* was an integer PHP will automatically treat the key as one, and not as a string with numeric values
                if ($keyToCompare === (string)$key) {
                    #Remove column key, since it's not required after this
                    if (!$keepKey) {
                        unset($item[$columnKey]);
                    }
                    $newArray[$key][] = $item;
                }
            }
        }
        return $newArray;
    }
}