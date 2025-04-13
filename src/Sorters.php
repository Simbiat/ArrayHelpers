<?php
declare(strict_types = 1);

namespace Simbiat\Arrays;

/**
 * Functions to sort arrays
 */
class Sorters
{
    /**
     * Function to sort a multidimensional array by values in column. Can be `reversed` to sort from larger to smaller (DESC order)
     * @param array  $array  Array to process
     * @param string $column Column to sort by
     * @param bool   $desc   Whether to use descending or ascending order
     *
     * @return array
     */
    public static function MultiArrSort(array $array, string $column, bool $desc = false): array
    {
        if (empty($column)) {
            throw new \InvalidArgumentException('Empty key (column) provided to MultiArrSort function.');
        }
        if ($desc === true) {
            #Order in DESC
            uasort($array, static function ($a, $b) use (&$column) {
                return $b[$column] <=> $a[$column];
            });
        } else {
            #Order in ASC
            uasort($array, static function ($a, $b) use (&$column) {
                return $a[$column] <=> $b[$column];
            });
        }
        return $array;
    }
}