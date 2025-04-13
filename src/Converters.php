<?php
declare(strict_types = 1);

namespace Simbiat\Arrays;

use function count;
use function is_string;

/**
 * Functions that covert stuff to/from arrays
 */
class Converters
{
    /**
     * Function allows turning multidimensional arrays to regular ones by "overwriting" each "row" with value from chosen column.
     * @param array  $oldArray  Array to process
     * @param string $keyToSave Column name to use
     *
     * @return array
     */
    public static function MultiToSingle(array $oldArray, string $keyToSave): array
    {
        if (empty($keyToSave)) {
            throw new \InvalidArgumentException('Empty key provided to MultiToSingle function.');
        }
        #Setting the empty array as precaution
        $newArray = [];
        #Iterrating the array provided
        foreach ($oldArray as $oldKey => $item) {
            #Adding the element to new array
            $newArray[$oldKey] = $item[$keyToSave];
        }
        return $newArray;
    }
    
    #Supressing inspection for functions related to DBase, since we have our own handler of lack of DBase extension
    /** @noinspection PhpUndefinedFunctionInspection */
    /**
     * Function to convert DBASE (.dbf) file to array
     * @param string $file
     *
     * @return array
     */
    public static function dbfToArray(string $file): array
    {
        if (!file_exists($file)) {
            throw new \UnexpectedValueException('File \''.$file.'\' provided to dbfToArray function is not found.');
        }
        if (\extension_loaded('dbase') === false) {
            throw new \RuntimeException('dbase extension required for dbfToArray function is not loaded.');
        }
        #Setting the empty array as precaution
        $array = [];
        #Open file for reading
        $dbf = dbase_open($file, 0);
        if ($dbf !== false) {
            #Get number of records in file
            $record_numbers = dbase_numrecords($dbf);
            if ($record_numbers === false) {
                throw new \RuntimeException('Failed to get number of records in \''.$file.'\' provided to dbfToArray function.');
            }
            #Iterrate the records
            for ($i = 1; $i <= $record_numbers; $i++) {
                #Add record to array
                $array[] = dbase_get_record_with_names($dbf, $i);
            }
            #Close file
            dbase_close($dbf);
        } else {
            throw new \RuntimeException('Failed to open \''.$file.'\' provided to dbfToArray function.');
        }
        return $array;
    }
    
    /**
     * Function to convert DOMNode into array with set of attributes, present in the node
     * @param \DOMNode $node            Node to process
     * @param bool     $null            Whether to replace empty strings with NULL
     * @param array    $extraAttributes List of attributes to add as either `null` (if `$null` is `true`) or empty string, if the attribute is missing
     *
     * @return array
     */
    public static function attributesToArray(\DOMNode $node, bool $null = true, array $extraAttributes = []): array
    {
        $result = [];
        #Iterrate attributes of the node
        foreach ($node->attributes as $attrName => $attrValue) {
            if ($null && $attrValue === '') {
                #Add to resulting array as NULL, if it's empty string
                $result[$attrName] = NULL;
            } else {
                #Add actual value
                $result[$attrName] = $attrValue->textContent;
            }
        }
        #Add any additional attributes, that are expected
        if (!empty($extraAttributes)) {
            foreach ($extraAttributes as $attribute) {
                if (!isset($result[$attribute])) {
                    if ($null) {
                        #Add as NULL
                        $result[$attribute] = NULL;
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
     * Convert a regular array into multidimensional one by turning keys into one of the columns
     * @param array $array Array to process
     * @param array $keys  New keys' names
     *
     * @return array
     */
    public static function toMultiArray(array $array, array $keys): array
    {
        if (count($keys) !== 2) {
            throw new \UnexpectedValueException('Number of keys provided does not equal 2');
        }
        $newArray = [];
        foreach ($array as $key => $element) {
            $newArray[] = [$keys[0] => $key, $keys[1] => $element];
        }
        return $newArray;
    }
    
    /**
     * Convert an array to object properties
     *
     * @param object $object Object to update
     * @param array  $array  Array of properties
     * @param array  $skip   Properties to skip
     * @param bool   $strict Whether property needs to exist
     *
     * @return void
     */
    public static function arrayToProperties(object $object, array $array, array $skip = [], bool $strict = true): void
    {
        #Iterrate the array
        foreach ($array as $key => $value) {
            #Check that key is string and not in list of keys to skip
            if (is_string($key) && !\in_array($key, $skip, true)) {
                #Throw an error if a property does not exist, and we use a strict mode
                if ($strict && property_exists($object, $key) !== true) {
                    throw new \LogicException(\get_class($object).' must have declared `'.$key.'` property.');
                }
                #Set property (or, at least, attempt to)
                $object->{$key} = $value;
            }
        }
    }
}