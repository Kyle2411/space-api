<?php

namespace Vanier\Api\Helpers;

/**
 * Helper for Simple Array Manipulation
 */
class ArrayHelper {
    /**
     * Remove All Unspecified Array Keys
     * @param array $array Array with Key-Value Pairs
     * @param array $keys Key of Key-Value Pairs to Keep
     * @return array $array with Only Specified Keys
     */
    public static function filterKeys(array $array, array $keys) 
    {
        $filteredArray = array_filter($array, function($key) use ($keys) {
            return in_array($key, $keys);
        }, ARRAY_FILTER_USE_KEY);

        return $filteredArray;
    }

    /**
     * Keep All Unspecified Array Keys
     * @param array $array Array with Key-Value Pairs
     * @param array $keys Key of Key-Value Pairs to Keep
     * @return array $array with Only Specified Keys
     */
    public static function keepMissingKeys(array $array, array $keys) 
    {
        $filteredArray = array_filter($array, function($key) use ($keys) {
            return !in_array($key, $keys);
        }, ARRAY_FILTER_USE_KEY);

        return $filteredArray;
    }

    /**
     * Validate Array Set Values
     * @param $values Array of Values to be Validated
     * @param $set_values Array of Set Values to Match
     * @return bool True or False Based on Values Being in Set
     */
    public static function validateSet($values, $set_values) {
        // If Values Aren't An Array...
        if (!is_array($values)) {
            // Convert Values Into Array
            $values = explode(',', $values);
        }

        // Remove Empty Values from Array
        $values = array_filter($values);

        // Check If All Values in Array are Part of Set
        foreach ($values as $value) {
            if (!in_array($value, $set_values)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check If Array Is Associate or Not
     * @param $array Array to Check
     * @return bool True or False Based on If Is An Associative Array or Not
     */
    public static function isAssociative(array $array) {
        // If Array Is Empty...
        if ($array == []) {
            return true;
        }

        $count = count($array);

        for ($i = 0; $i < $count; $i++) {
            // If Number Doesn't Exist...
            if(!array_key_exists($i, $array)) {
                return true;
            }
        }

        return false;
    }
}