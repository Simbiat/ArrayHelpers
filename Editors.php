<?php

declare(strict_types=1);

namespace Simbiat\ArrayHelpers;

use JetBrains\PhpStorm\ExpectedValues;

/**
 * Function to edit arrays
 */
final class Editors
{
    /**
     * Function allows turning regular arrays (keyed 0, 1, 2, ... n) to associative one using values from the column provided as the 2nd argument. Can remove that column from new arrays. Useful for structuring results from some complex SELECT, when you know that each row returned is a separate entity.
     *
     * @param array  $old_array Array to process
     * @param string $new_key   Key to use values from
     * @param bool   $key_unset Whether to remove the original key
     *
     * @return array
     */
    public static function digitToKey(array $old_array, string $new_key, bool $key_unset = false): array
    {
        if (empty($new_key)) {
            throw new \InvalidArgumentException('Empty key provided to DigitToKey function.');
        }
        // Setting the empty array as a precaution
        $new_array = \array_column($old_array, null, $new_key);
        // Removing the old column
        if ($key_unset) {
            foreach ($new_array as $key => $item) {
                unset($new_array[$key][$new_key]);
            }
        }

        return $new_array;
    }

    /**
     * Function casts a set of selected columns' values to the chosen type (INT by default). Initially created due to MySQL enforcing string values instead of integers in a lot of cases.
     *
     * @param array        $to_process Array to process
     * @param array|string $columns    Column(s) to cast
     * @param string       $type       Type to cast to
     *
     * @return array
     */
    public static function columnsConversion(array $to_process, array|string $columns, #[ExpectedValues(['int', 'integer', 'bool', 'boolean', 'float', 'double', 'real', 'string', 'array', 'object'])] string $type = 'int'): array
    {
        // Checking values
        if (empty($columns)) {
            throw new \InvalidArgumentException('Empty array provided to ColumnsToInt function.');
        }
        if (\is_string($columns)) {
            $columns = [$columns];
        }
        if (!\is_array($columns)) {
            throw new \InvalidArgumentException('Columns provided to ColumnsToInt function are neither string nor array.');
        }
        // Iterating the array provided
        foreach ($to_process as $key => $value) {
            // Iterating columns' list provided
            foreach ($columns as $column) {
                // Casting element based on the type
                $to_process[$key][$column] = match ($type) {
                    'int', 'integer' => (int) $value[$column],
                    'bool', 'boolean' => (bool) $value[$column],
                    'float', 'double', 'real' => (float) $value[$column],
                    'string' => (string) $value[$column],
                    'array' => (array) $value[$column],
                    'object' => (object) $value[$column],
                    default => null,
                };
            }
        }

        return $to_process;
    }

    /**
     * Simple function that removes all elements with a certain value and optionally re-keys it (useful for an indexed array, useless for associative ones)
     *
     * @param array $to_process   Array to process
     * @param mixed $remove_value Value to remove based on
     * @param bool  $rekey        Whether to rekey the array
     *
     * @return array
     */
    public static function removeByValue(array $to_process, mixed $remove_value, bool $rekey = false): array
    {
        // Iterating the array provided
        foreach ($to_process as $key => $value) {
            // Compare either strictly or not, depending on the flag provided
            if ($value === $remove_value) {
                unset($to_process[$key]);
            }
        }
        // Rekey the array
        if ($rekey) {
            $to_process = \array_values($to_process);
        }

        return $to_process;
    }

    /**
     * Function to move keys into a subarray. For example, you have a key like $to_process['key'], but you want to remove it and have it as $to_process['subarray']['key'] - then use this function. Purely for data formatting.
     *
     * @param array      $to_process   Array to process
     * @param string|int $key          Key to move
     * @param array      $new_key_path Array where each key is part of a new path ($to_process['new', 'path'] is meant to be converted to result in $to_process['new']['path'])
     *
     * @return void
     */
    public static function moveToSubarray(array &$to_process, string|int $key, array $new_key_path): void
    {
        // Modify only if the key exists
        if (\array_key_exists($key, $to_process)) {
            // Copy the value
            self::setKeyPath($to_process, $new_key_path, $to_process[$key]);
            // Remove the original key
            unset($to_process[$key]);
        }
    }

    /**
     * Allows recursively setting a key path. Based on https://stackoverflow.com/a/5821027/2992851
     *
     * @param array $to_process Array to process (passed by reference)
     * @param array $path       Array where each key is part of a new path (['new', 'path'] is meant to be converted and result in $to_process['new']['path'])
     * @param mixed $value      Value to assign to the new key
     *
     * @return void
     */
    public static function setKeyPath(array &$to_process, array $path, mixed $value): void
    {
        $key = \array_shift($path);
        if (empty($path)) {
            $to_process[$key] = $value;
        } else {
            if (
                !\array_key_exists($key, $to_process)
                || !\is_array($to_process[$key])
            ) {
                $to_process[$key] = [];
            }
            self::setKeyPath($to_process[$key], $path, $value);
        }
    }

    /**
     * Rename a column in a multidimensional array
     *
     * @param array  $to_process Array to process
     * @param string $column     Column name
     * @param string $key_name   New kew name
     *
     * @return void
     */
    public static function renameColumn(array &$to_process, string $column, string $key_name): void
    {
        foreach ($to_process as $key => $row) {
            $to_process[$key][$key_name] = $row[$column];
            unset($to_process[$key][$column]);
        }
    }
}
