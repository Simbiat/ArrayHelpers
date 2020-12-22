<?php
declare(strict_types=1);
namespace ArrayHelpers;

class ArrayHelpers
{
    #Function that splits the array to 2 representing first X and last X rows from it, providing a way to get 'Top X' and its counterpart
    public function topAndBottom(array $array, int $rows = 0): array
    {
        if (empty($array)) {
            throw new \InvalidArgumentException('Empty array provided to topAndBottom function.');
        }
        if (count($array) === 1) {
            throw new \UnexpectedValueException('Array provided to topAndBottom function contains only 1 element.');
        }
        #If number of rows sent is <=0 or the amount of elements is lower than the number of rows x2, attempt to split evenly
        if ($rows <= 0 || count($array) < ($rows*2)) {
            $rows = floor(count($array)/2);
        }
        $newarray['top'] = array_slice($array, 0, $rows);
        $newarray['bottom'] = array_reverse(array_slice($array, -$rows, $rows));
        return $newarray;
    }
    
    #Useful to reduce number of travels to database. Instead of doing 2+ queries separately, we do just 1 query and then split it to several arrays in code
    public function splitByKey(array $array, string $columnkey, array $newkeys, array $valuestocheck): array
    {
        #Checking values
        if (empty($array)) {
            throw new \InvalidArgumentException('Empty array provided to splitByKey function.');
        }
        if (empty($columnkey)) {
            throw new \InvalidArgumentException('Empty key provided to splitByKey function.');
        }
        if (empty($newkeys)) {
            throw new \InvalidArgumentException('Empty array of new keys provided to splitByKey function.');
        }
        if (empty($valuestocheck)) {
            throw new \InvalidArgumentException('Empty array of expected values provided to splitByKey function.');
        }
        #Checking that length of both keys and values is same
        if (count($newkeys) !== count($valuestocheck)) {
            throw new \UnexpectedValueException('List of keys and expected arrays provided to splitByKey function have different number of elements.');
        }
        #Prepare empty array
        foreach ($newkeys as $arrkey=>$newkey) {
            if (empty($newkey)) {
                throw new \UnexpectedValueException('New key with index value \''.$arrkey.'\' is empty and cannot be used as key for new array by splitByKey function.');
            } else {
                if (is_string($newkey) || is_int($newkey)) {
                    $newarray[$newkey] = [];
                } else {
                    throw new \UnexpectedValueException('New key with index value \''.$arrkey.'\' is neither string nor integer and cannot be used as key for new array by splitByKey function.');
                }
            }
        }
        #Combine keys and values for easier identification, where a value should go to new array
        $valuestocheck = array_combine($newkeys, $valuestocheck);
        foreach ($valuestocheck as $key=>$value) {
            foreach ($array as $item) {
                #Value in item
                if ($item[$columnkey] == $value) {
                    #Remove column key, since it's not required after this
                    unset($item[$columnkey]);
                    $newarray[$key][] = $item;
                }
            }
        }
        return $newarray;
    }
    
    #Function allows to turn regular arrays (keyed 0, 1, 2, ... n) to assotiative one using values from the column provided as 2nd argument. Has option to remove that column from new arrays. Useful for structuring results from some complex SELECTs, when you know that each row returned is a separate entity
    public function DigitToKey(array $oldarray, string $newkey, bool $keyunset = false): array
    {
        if (empty($newkey)) {
            throw new \InvalidArgumentException('Empty key provided to DigitToKey function.');
        }
        #Setting the empty array as precaution
        $newarray = [];
        #Iterrating the array provided
        foreach ($oldarray as $oldkey=>$item) {
            #Adding the element to new array
            $newarray[$oldarray[$oldkey][$newkey]] = $oldarray[$oldkey];
            #Removing old column
            if ($keyunset == true) {
                unset($newarray[$oldarray[$oldkey][$newkey]][$newkey]);
            }
        }
        return $newarray;
    }
    
    #Function allows to turn multidimensional arrays to regular ones by "overwriting" each "row" with value from chosen column
    public function MultiToSingle(array $oldarray, string $keytosave): array
    {
        if (empty($keytosave)) {
            throw new \InvalidArgumentException('Empty key provided to MultiToSingle function.');
        }
        #Setting the empty array as precaution
        $newarray = [];
        #Iterrating the array provided
        foreach ($oldarray as $oldkey=>$item) {
            #Adding the element to new array
            $newarray[$oldkey] = $item[$keytosave];
        }
        return $newarray;
    }
    
    #Function converts set of selected columns' values to chosen type (INT by default). Initially created due to MySQL enforcing string values instead of integers in a lot of cases.
    public function ColumnsConversion(array $array, $columns, string $type = 'int'): array
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
                switch (strtolower($type)) {
                    case 'int':
                    case 'integer':
                        $array[$key][$column] = (int)$value[$column]; break;
                    case 'bool':
                    case 'boolean':
                        $array[$key][$column] = (bool)$value[$column]; break;
                    case 'float':
                    case 'double':
                    case 'real':
                        $array[$key][$column] = (float)$value[$column]; break;
                    case 'string':
                        $array[$key][$column] = (string)$value[$column]; break;
                    case 'array':
                        $array[$key][$column] = (array)$value[$column]; break;
                    case 'object':
                        $array[$key][$column] = (object)$value[$column]; break;
                    case 'uncset':
                    case 'null':
                    case 'nothing':
                        $array[$key][$column] = NULL; break;
                }
            }
        }
        return $array;
    }
    
    #Simple function, that removes all elements with certain value and optionally rekeys it (useful for indexed array, useless for assotiative ones)
    public function RemoveByValue(array $array, $remvalue, bool $strict = true, bool $rekey = false): array
    {
        #Iterrating the array provided
        foreach ($array as $key=>$value) {
            #Compare either strictly or not, depending on the flag provided
            if (($strict === true && $array[$key] === $remvalue) || ($strict === false && $array[$key] == $remvalue)) {
                unset($array[$key]);
            }
        }
        #Rekey the array
        if ($rekey === true) {
            $array = array_values($array);
        }
        return $array;
    }
    
    #Function to sort a multidimensional array by values in column. Can be `reversed` to sort from larger to smaler (DESC order)
    public function MultArrSort(array $array, string $column, bool $desc = false): array
    {
        if (empty($column)) {
            throw new \InvalidArgumentException('Empty key (column) provided to MultArrSort function.');
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
    public function dbfToArray(string $file): array
    {
        if (!file_exists($file)) {
            throw new \UnexpectedValueException('File \''.$file.'\' provided to dbfToArray function is not found.');
        }
        if (extension_loaded('dbase') === false) {
            throw new \RuntimeException('dbase extension requried for dbfToArray function is not loaded.');
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
}
?>