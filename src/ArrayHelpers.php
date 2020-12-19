<?php
declare(strict_types=1);
namespace ArrayHelpers;

class ArrayHelpers
{
    #Function that splits the array to 2 representing first X and last X rows from it, providing a way to get 'Top X' and its counterpart
    public function topAndBottom(array $array, int $rows = 0): array
    {
        if (empty($array)) {
            throw new \UnexpectedValueException('Empty array provided to topAndBottom function.');
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
            throw new \UnexpectedValueException('Empty array provided to splitByKey function.');
        }
        if (empty($columnkey)) {
            throw new \UnexpectedValueException('Empty key provided to splitByKey function.');
        }
        if (empty($newkeys)) {
            throw new \UnexpectedValueException('Empty array of new keys provided to splitByKey function.');
        }
        if (empty($valuestocheck)) {
            throw new \UnexpectedValueException('Empty array of expected values provided to splitByKey function.');
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
}
?>