<?php

declare(strict_types=1);

namespace Simbiat\ArrayHelpers;

/**
 * Functions to sort arrays
 */
final class Sorters
{
    /**
     * Function to sort a multidimensional array by values in a column. Can be "reversed" to sort from larger to smaller (DESC order)
     *
     * @param array  $to_sort Array to process
     * @param string $column  Column to sort by
     * @param bool   $desc    Whether to use descending or ascending order
     *
     * @return array
     */
    public static function multiArrSort(array $to_sort, string $column, bool $desc = false): array
    {
        if (empty($column)) {
            return [];
        }
        if ($desc) {
            // Order in DESC
            \uasort($to_sort, static function ($a, $b) use (&$column) {
                return $b[$column] <=> $a[$column];
            });
        } else {
            // Order in ASC
            \uasort($to_sort, static function ($a, $b) use (&$column) {
                return $a[$column] <=> $b[$column];
            });
        }

        return $to_sort;
    }

    /**
     * Recursively sort array (using `sort`, `rsort`, `ksort` or `krsort`)
     *
     * @param array $to_sort   Array to sort
     * @param bool  $key       Whether to sort by key or by value
     * @param bool  $desc      Whether to sort in descending order
     * @param int   $sort_flag Respective PHP's `SORT_*` flag to control logic of sort functions
     *
     * @return void
     */
    public static function recursiveSort(array &$to_sort, bool $key = false, bool $desc = false, int $sort_flag = \SORT_REGULAR): void
    {
        foreach ($to_sort as &$value) {
            if (\is_array($value)) {
                self::recursiveSort($value, $key, $desc, $sort_flag);
            }
        }
        unset($value);
        if ($key) {
            if ($desc) {
                \krsort($to_sort, $sort_flag);
            } else {
                \ksort($to_sort, $sort_flag);
            }
        } elseif ($desc) {
            \rsort($to_sort, $sort_flag);
        } else {
            \sort($to_sort, $sort_flag);
        }
    }
}
