<?php
declare(strict_types = 1);

namespace Simbiat\Arrays;

use JetBrains\PhpStorm\ExpectedValues;
use function array_key_exists;
use function is_array;
use function is_string;

/**
 * Function to edit arrays
 */
class Editors
{
    /**
     * Function allows turning regular arrays (keyed 0, 1, 2, ... n) to associative one using values from the column provided as 2nd argument. Has option to remove that column from new arrays. Useful for structuring results from some complex SELECT, when you know that each row returned is a separate entity.
     * @param array  $oldArray Array to process
     * @param string $newKey   Key to use values from
     * @param bool   $keyUnset Whether to remove original key
     *
     * @return array
     */
    public static function DigitToKey(array $oldArray, string $newKey, bool $keyUnset = false): array
    {
        if (empty($newKey)) {
            throw new \InvalidArgumentException('Empty key provided to DigitToKey function.');
        }
        #Setting the empty array as precaution
        $newArray = [];
        #Iterrating the array provided
        foreach ($oldArray as $item) {
            #Adding the element to new array
            $newArray[$item[$newKey]] = $item;
            #Removing old column
            if ($keyUnset === true) {
                unset($newArray[$item[$newKey]][$newKey]);
            }
        }
        return $newArray;
    }
    
    /**
     * Function converts set of selected columns' values to chosen type (INT by default). Initially created due to MySQL enforcing string values instead of integers in a lot of cases.
     * @param array        $array   Array to process
     * @param array|string $columns Column(s) to convert
     * @param string       $type    Type to convert to
     *
     * @return array
     */
    public static function ColumnsConversion(array $array, array|string $columns, #[ExpectedValues(['int', 'integer', 'bool', 'boolean', 'float', 'double', 'real', 'string', 'array', 'object'])] string $type = 'int'): array
    {
        #Checking values
        if (empty($columns)) {
            throw new \InvalidArgumentException('Empty array provided to ColumnsToInt function.');
        }
        if (is_string($columns)) {
            $columns = [$columns];
        }
        if (!is_array($columns)) {
            throw new \InvalidArgumentException('Columns provided to ColumnsToInt function are neither string nor array.');
        }
        #Iterrating the array provided
        foreach ($array as $key => $value) {
            #Iterrating columns' list provided
            foreach ($columns as $column) {
                #Converting element based on the type
                $array[$key][$column] = match ($type) {
                    'int', 'integer' => (int)$value[$column],
                    'bool', 'boolean' => (bool)$value[$column],
                    'float', 'double', 'real' => (float)$value[$column],
                    'string' => (string)$value[$column],
                    'array' => (array)$value[$column],
                    'object' => (object)$value[$column],
                    default => NULL,
                };
            }
        }
        return $array;
    }
    
    /**
     * Simple function, that removes all elements with certain value and optionally re-keys it (useful for indexed array, useless for associative ones)
     * @param array $array    Array to process
     * @param mixed $remValue Value to remove based on
     * @param bool  $reKey    Whether to re-key the array
     *
     * @return array
     */
    public static function RemoveByValue(array $array, mixed $remValue, bool $reKey = false): array
    {
        #Iterrating the array provided
        foreach ($array as $key => $value) {
            #Compare either strictly or not, depending on the flag provided
            if ($value === $remValue) {
                unset($array[$key]);
            }
        }
        #Rekey the array
        if ($reKey) {
            $array = array_values($array);
        }
        return $array;
    }
    
    /**
     * Function to move keys into a subarray. For example, you have a key like $array['key'], but you want to remove it and have it as $array['subarray']['key'] - then use this function. Purely for data formatting.
     * @param array      $array      Array to process
     * @param string|int $key        Key to move
     * @param array      $newKeyPath Array where each key is part of a new path ($array['new', 'path'] is meant to be converted to result in $array['new']['path'])
     *
     * @return void
     */
    public static function moveToSubarray(array &$array, string|int $key, array $newKeyPath): void
    {
        #Modify only if key exists
        if (array_key_exists($key, $array)) {
            #Copy the value
            self::setKeyPath($array, $newKeyPath, $array[$key]);
            #Remove original key
            unset($array[$key]);
        }
    }
    
    /**
     * Allows to recursively set a key path. Based on https://stackoverflow.com/a/5821027/2992851
     * @param array $array Array to process
     * @param array $path  Array where each key is part of a new path (['new', 'path'] is meant to be converted and result in $array['new']['path'])
     * @param mixed $value Value to assign to the new key
     *
     * @return void
     */
    public static function setKeyPath(array &$array, array $path, mixed $value): void
    {
        $key = array_shift($path);
        if (empty($path)) {
            $array[$key] = $value;
        } else {
            if (!array_key_exists($key, $array) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            self::setKeyPath($array[$key], $path, $value);
        }
    }
    
    /**
     * Rename column in array
     * @param array  $array   Array to process
     * @param string $column  Column name
     * @param string $keyName New kew name
     *
     * @return void
     */
    public static function renameColumn(array &$array, string $column, string $keyName): void
    {
        foreach ($array as $key => $row) {
            $array[$key][$keyName] = $row[$column];
            unset($array[$key][$column]);
        }
    }
}
