<?php

declare(strict_types=1);

namespace Simbiat\ArrayHelpers;

/**
 * Functions that check something about arrays
 */
final class Checkers
{
    /**
     * Check if an array is multidimensional
     *
     * @param array $to_check     Array to check
     * @param bool  $equal_length Whether to check that all rows are of the same length
     * @param bool  $all_scalar   Whether to check that all values are scalar
     *
     * @return bool
     */
    public static function isMultiDimensional(array $to_check, bool $equal_length = false, bool $all_scalar = false): bool
    {
        // Check if multidimensional
        if (\count(\array_filter(\array_values($to_check), '\is_array')) === \count($to_check)) {
            // Check if all child arrays have the same length
            if ($equal_length) {
                if (\count(\array_unique(\array_map('\count', $to_check))) !== 1) {
                    throw new \UnexpectedValueException('Not all child arrays have same length.');
                }

                return true;
            }

            return true;
        }
        // Check that all values are scalars
        if (
            $all_scalar
            && !self::isAllScalar($to_check)
        ) {
            throw new \UnexpectedValueException('Array contains both scalar and non-scalar values.');
        }

        return false;
    }

    /**
     * Check if an array is associative
     *
     * @param array $to_check
     *
     * @return bool
     */
    public static function isAssociative(array $to_check): bool
    {
        return \array_any(\array_keys($to_check), static fn($key) => \is_string($key));
    }

    /**
     * Check if all values of an array are scalar
     *
     * @param array $to_check Array to check
     *
     * @return bool
     */
    public static function isAllScalar(array $to_check): bool
    {
        // Check that all values are scalars
        return !\array_any($to_check, static fn($value) => !\is_scalar($value));
    }

    /**
     * Get list of changes in the new array compared to the old one
     *
     * @param array $old_array
     * @param array $new_array
     *
     * @return array
     */
    public static function getChanges(array $old_array, array $new_array): array
    {
        $changes = [];
        foreach ($old_array as $key => $value) {
            if (\array_key_exists($key, $new_array)) {
                if ($new_array[$key] !== $value) {
                    $changes[$key] = ['from' => $value, 'to' => $new_array[$key]];
                }
                // Remove the key to shorten next loop
                unset($new_array[$key]);
            } else {
                $changes[$key] = ['from' => $value, 'to' => null];
            }
        }
        foreach ($new_array as $key => $value) {
            $changes[$key] = ['from' => null, 'to' => $value];
        }

        return $changes;
    }
}
