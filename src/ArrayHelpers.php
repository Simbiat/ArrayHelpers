<?php
declare(strict_types=1);
namespace Simbiat;

class ArrayHelpers
{
    #Function that splits the array to 2 representing first X and last X rows from it, providing a way to get 'Top X' and its counterpart
    public static function topAndBottom(array $array, int $rows = 0): array
    {
        if (empty($array)) {
            throw new \InvalidArgumentException('Empty array provided to topAndBottom function.');
        }
        if (count($array) === 1) {
            throw new \UnexpectedValueException('Array provided to topAndBottom function contains only 1 element.');
        }
        #If number of rows sent is <=0 or the amount of elements is lower than the number of rows x2, attempt to split evenly
        if ($rows <= 0 || count($array) < ($rows*2)) {
            $rows = intval(floor(count($array)/2));
        }
        $newArray['top'] = array_slice($array, 0, $rows);
        $newArray['bottom'] = array_reverse(array_slice($array, -$rows, $rows));
        return $newArray;
    }

    #Useful to reduce number of travels to database. Instead of doing 2+ queries separately, we do just 1 query and then split it to several arrays in code
    public static function splitByKey(array $array, string $columnKey, array $newKeys, array $valuesToCheck): array
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
        if (empty($valuesToCheck)) {
            $valuesToCheck = $newKeys;
        }
        #Checking that length of both keys and values is same
        if (count($newKeys) !== count($valuesToCheck)) {
            throw new \UnexpectedValueException('List of keys and expected arrays provided to splitByKey function have different number of elements.');
        }
        #Prepare empty array
        foreach ($newKeys as $arrKey=>$newKey) {
            if (empty($newKey)) {
                throw new \UnexpectedValueException('New key with index value \''.$arrKey.'\' is empty and cannot be used as key for new array by splitByKey function.');
            } else {
                if (is_string($newKey) || is_int($newKey)) {
                    $newArray[$newKey] = [];
                } else {
                    throw new \UnexpectedValueException('New key with index value \''.$arrKey.'\' is neither string nor integer and cannot be used as key for new array by splitByKey function.');
                }
            }
        }
        #Combine keys and values for easier identification, where a value should go to new array
        $valuesToCheck = array_combine($newKeys, $valuesToCheck);
        foreach ($valuesToCheck as $key=> $value) {
            foreach ($array as $item) {
                #Value in item
                if ($item[$columnKey] == $value) {
                    #Remove column key, since it's not required after this
                    unset($item[$columnKey]);
                    $newArray[$key][] = $item;
                }
            }
        }
        return $newArray;
    }

    #Function allows turning regular arrays (keyed 0, 1, 2, ... n) to associative one using values from the column provided as 2nd argument. Has option to remove that column from new arrays. Useful for structuring results from some complex SELECT, when you know that each row returned is a separate entity
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
            if ($keyUnset == true) {
                unset($newArray[$item[$newKey]][$newKey]);
            }
        }
        return $newArray;
    }

    #Function allows turning multidimensional arrays to regular ones by "overwriting" each "row" with value from chosen column
    public static function MultiToSingle(array $oldArray, string $keyToSave): array
    {
        if (empty($keyToSave)) {
            throw new \InvalidArgumentException('Empty key provided to MultiToSingle function.');
        }
        #Setting the empty array as precaution
        $newArray = [];
        #Iterrating the array provided
        foreach ($oldArray as $oldKey=>$item) {
            #Adding the element to new array
            $newArray[$oldKey] = $item[$keyToSave];
        }
        return $newArray;
    }

    #Function converts set of selected columns' values to chosen type (INT by default). Initially created due to MySQL enforcing string values instead of integers in a lot of cases.
    public static function ColumnsConversion(array $array, array|string $columns, string $type = 'int'): array
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
        foreach ($array as $key=>$value) {
            #Iterrating columns' list provided
            foreach ($columns as $column) {
                #Converting element based on the type
                $array[$key][$column] = match($type) {
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

    #Simple function, that removes all elements with certain value and optionally re-keys it (useful for indexed array, useless for associative ones)
    public static function RemoveByValue(array $array, mixed $remValue, bool $strict = true, bool $reKey = false): array
    {
        #Iterrating the array provided
        foreach ($array as $key=>$value) {
            #Compare either strictly or not, depending on the flag provided
            if (($strict === true && $value === $remValue) || ($strict === false && $value == $remValue)) {
                unset($array[$key]);
            }
        }
        #Rekey the array
        if ($reKey === true) {
            $array = array_values($array);
        }
        return $array;
    }

    #Function to sort a multidimensional array by values in column. Can be `reversed` to sort from larger to smaller (DESC order)
    public static function MultiArrSort(array $array, string $column, bool $desc = false): array
    {
        if (empty($column)) {
            throw new \InvalidArgumentException('Empty key (column) provided to MultiArrSort function.');
        }
        if ($desc === true) {
            #Order in DESC
            uasort($array, function($a, $b) use(&$column) {
                    return $b[$column] <=> $a[$column];
            });
        } else {
            #Order in ASC
            uasort($array, function($a, $b) use(&$column) {
                    return $a[$column] <=> $b[$column];
            });
        }
        return $array;
    }

    #Function to convert DBASE (.dbf) file to array
    #Supressing inspection for functions related to DBase, since we have our own handler of lack of DBase extension
    /** @noinspection PhpUndefinedFunctionInspection */
    public static function dbfToArray(string $file): array
    {
        if (!file_exists($file)) {
            throw new \UnexpectedValueException('File \''.$file.'\' provided to dbfToArray function is not found.');
        }
        if (extension_loaded('dbase') === false) {
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

    #Function to convert DOMNode into array with set of attributes, present in the node
    #$null will replace empty strings with NULL, if set to true
    #$extraAttributes will add any missing attributes as NULL or empty strings. Useful for standardization
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

    #Function to move keys into a subarray.
    #For example, you have a key like $array['key'], but you want to remove it and have it as $array['subarray']['key'] - then use this function
    #Purely for data formatting.
    #$newKeyPath should be an array where each key is part of a new path ($array['new', 'path'] is meant to be converted to result in $array['new']['path'])/
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

    #Allows to recursively set a key path. Based on https://stackoverflow.com/a/5821027/2992851
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

    #Rename column in array
    public static function renameColumn(array &$array, string $column, string $keyName): void
    {
        foreach($array as $key=>$row) {
            $array[$key][$keyName] = $row[$column];
            unset($array[$key][$column]);
        }
    }

    #Convert a regular array into multidimensional one by turning keys into one of the columns
    public static function toMultiArray(array $array, array $keys): array
    {
        if (count($keys) !== 2) {
            throw new \UnexpectedValueException('Number of keys provided does not equal 2');
        }
        $newArray = [];
        foreach ($array as $key=>$element) {
            $newArray[] = [$keys[0] => $key, $keys[1] => $element];
        }
        return $newArray;
    }
}
