<?php
declare(strict_types = 1);

namespace Simbiat\Arrays;

/**
 * Functions to sort arrays
 */
class Sorters
{
    /**
     * Function to sort a multidimensional array by values in a column. Can be "reversed" to sort from larger to smaller (DESC order)
     * @param array  $array  Array to process
     * @param string $column Column to sort by
     * @param bool   $desc   Whether to use descending or ascending order
     *
     * @return array
     */
    public static function multiArrSort(array $array, string $column, bool $desc = false): array
    {
        if (empty($column)) {
            return [];
        }
        if ($desc) {
            #Order in DESC
            \uasort($array, static function ($a, $b) use (&$column) {
                return $b[$column] <=> $a[$column];
            });
        } else {
            #Order in ASC
            \uasort($array, static function ($a, $b) use (&$column) {
                return $a[$column] <=> $b[$column];
            });
        }
        return $array;
    }
    
    /**
     * Recursively sort array (using `sort`, `rsort`, `ksort` or `krsort`)
     * @param array $array     Array to sort
     * @param bool  $key       Whether to sort by key or by value
     * @param bool  $desc      Whether to sort in descending order
     * @param int   $sort_flag Respective PHP's `SORT_*` flag to control logic of sort functions
     *
     * @return void
     */
    public static function recursiveSort(array &$array, bool $key = false, bool $desc = false, int $sort_flag = \SORT_REGULAR): void
    {
        foreach ($array as &$value) {
            if (\is_array($value)) {
                self::recursiveSort($value, $key, $desc, $sort_flag);
            }
        }
        unset($value);
        if ($key) {
            if ($desc) {
                \krsort($array, $sort_flag);
            } else {
                \ksort($array, $sort_flag);
            }
        } elseif ($desc) {
            \rsort($array, $sort_flag);
        } else {
            \sort($array, $sort_flag);
        }
    }
}