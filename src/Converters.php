<?php
declare(strict_types = 1);

namespace Simbiat\Arrays;

use Dom\Node;
use function count;
use function is_string;

/**
 * Functions that covert stuff to/from arrays
 */
class Converters
{
    /**
     * Function allows turning multidimensional arrays to regular ones by "overwriting" each "row" with value from chosen column.
     *
     * @param array  $old_array   Array to process
     * @param string $key_to_save Column name to use
     *
     * @return array
     */
    public static function multiToSingle(array $old_array, string $key_to_save): array
    {
        if (\preg_match('/^\s*$/', $key_to_save)) {
            return [];
        }
        return \array_combine(\array_keys($old_array), \array_column($old_array, $key_to_save));
    }
    
    #Supressing inspection for functions related to DBase, since we have our own handler logic for this
    /** @noinspection PhpUndefinedFunctionInspection */
    /**
     * Function to convert a DBASE (.dbf) file to array
     * @param string $file
     *
     * @return array
     */
    public static function dbfToArray(string $file): array
    {
        if (!\extension_loaded('dbase')) {
            throw new \RuntimeException('dbase extension required for dbfToArray function is not loaded.');
        }
        if (!\is_file($file)) {
            throw new \UnexpectedValueException('File \''.$file.'\' provided to dbfToArray function is not found.');
        }
        #Setting the empty array as a precaution
        $array = [];
        #Open the file for reading
        $dbf = dbase_open($file, 0);
        if ($dbf !== false) {
            #Get the number of records in the file
            $record_numbers = dbase_numrecords($dbf);
            if ($record_numbers === false) {
                throw new \RuntimeException('Failed to get number of records in \''.$file.'\' provided to dbfToArray function.');
            }
            #Iterrate the records
            for ($iter = 1; $iter <= $record_numbers; $iter++) {
                #Add record to array
                $array[] = dbase_get_record_with_names($dbf, $iter);
            }
            #Close file
            dbase_close($dbf);
        } else {
            throw new \RuntimeException('Failed to open \''.$file.'\' provided to dbfToArray function.');
        }
        return $array;
    }
    
    /**
     * Function to convert DOMNode into an array with a set of attributes, present in the node, as the array's keys.
     *
     * @param \DOMNode|\Dom\Node $node             Node to process
     * @param bool               $null             Whether to replace empty strings with `null`
     * @param array              $extra_attributes List of attributes to add as either `null` (if `$null` is `true`) or empty string, if the attribute is missing
     *
     * @return array
     */
    public static function attributesToArray(\DOMNode|Node $node, bool $null = true, array $extra_attributes = []): array
    {
        $result = [];
        #Iterrate attributes of the node
        foreach ($node->attributes as $attribute_name => $attribute_value) {
            if ($null && $attribute_value === '') {
                #Add to the resulting array as null if it's an empty string
                $result[$attribute_name] = null;
            } else {
                #Add actual value
                $result[$attribute_name] = $attribute_value->textContent;
            }
        }
        #Add any additional attributes that are expected
        if (count($extra_attributes) > 0) {
            foreach ($extra_attributes as $attribute) {
                if (!\array_key_exists($attribute, $result)) {
                    if ($null) {
                        #Add as null
                        $result[$attribute] = null;
                    } else {
                        #Or add as empty string
                        $result[$attribute] = '';
                    }
                }
            }
        }
        #Return resulting string
        return $result;
    }
    
    /**
     * Convert a regular array into a multidimensional one by turning provided keys into one of the columns
     * @param array $array Array to process.
     * @param array $keys  New keys' names. Has to be an array of 2 strings or integer elements.
     *
     * @return array
     */
    public static function toMultiArray(array $array, array $keys): array
    {
        if (count($keys) !== 2) {
            throw new \UnexpectedValueException('Number of keys provided does not equal 2');
        }
        $new_array = [];
        foreach ($array as $key => $element) {
            $new_array[] = [$keys[0] => $key, $keys[1] => $element];
        }
        return $new_array;
    }
    
    /**
     * Convert an array to object properties
     *
     * @param object $object Object to update.
     * @param array  $array  Array of properties. Only values with string keys will be processed.
     * @param array  $skip   Properties to skip.
     * @param bool   $strict Whether property needs to exist.
     *
     * @return void
     */
    public static function arrayToProperties(object $object, array $array, array $skip = [], bool $strict = true): void
    {
        #Iterrate the array
        foreach ($array as $key => $value) {
            #Check that a key is string and not in the list of keys to skip
            if (is_string($key) && !\in_array($key, $skip, true)) {
                #Throw an error if a property does not exist, and we use a strict mode
                if ($strict && !\property_exists($object, $key)) {
                    throw new \LogicException(\get_class($object).' must have declared `'.$key.'` property.');
                }
                #Set property (or, at least, attempt to)
                $object->{$key} = $value;
            }
        }
    }
    
    /**
     * Get values of a backed enum's or names of non-backed enum's cases
     * @param string $enum Enum to get values from
     *
     * @return array
     */
    public static function enumValues(string $enum): array
    {
        if (\is_subclass_of($enum, \BackedEnum::class)) {
            return \array_map(
                static fn(\BackedEnum $case) => $case->value, $enum::cases()
            );
        }
        if (\is_subclass_of($enum, \UnitEnum::class)) {
            return \array_map(
                static fn(\UnitEnum $case) => $case->name, $enum::cases()
            );
        }
        throw new \InvalidArgumentException($enum.' is not an enum');
    }
}