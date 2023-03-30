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
}