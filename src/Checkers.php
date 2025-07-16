<?php
declare(strict_types = 1);

namespace Simbiat\Arrays;

use function count;
use function is_string;

/**
 * Functions that check something about arrays
 */
class Checkers
{
    /**
     * Check if an array is multidimensional
     *
     * @param array $array        Array to check
     * @param bool  $equal_length Whether to check that all rows are of the same length
     * @param bool  $all_scalar   Whether to check that all values are scalar
     *
     * @return bool
     */
    public static function isMultiDimensional(array $array, bool $equal_length = false, bool $all_scalar = false): bool
    {
        #Check if multidimensional
        if (count(array_filter(array_values($array), 'is_array')) === count($array)) {
            #Check if all child arrays have the same length
            if ($equal_length) {
                if (count(array_unique(array_map('count', $array))) !== 1) {
                    throw new \UnexpectedValueException('Not all child arrays have same length.');
                }
                return true;
            }
            return true;
        }
        #Check that all values are scalars
        if ($all_scalar && !self::isAllScalar($array)) {
            throw new \UnexpectedValueException('Array contains both scalar and non-scalar values.');
        }
        return false;
    }
    
    /**
     * Check if an array is associative
     *
     * @param array $array
     *
     * @return bool
     */
    public static function isAssociative(array $array): bool
    {
        return array_any(array_keys($array), static fn($key) => is_string($key));
    }
    
    /**
     * Check if all values of an array are scalar
     *
     * @param array $array Array to check
     *
     * @return bool
     */
    public static function isAllScalar(array $array): bool
    {
        #Check that all values are scalars
        return !array_any($array, static fn($value) => !is_scalar($value));
    }
}