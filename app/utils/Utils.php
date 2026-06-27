<?php

namespace App\Utils;


class Utils
{

    /**
     * Returns array with max array's value index and value itself
     *
     * @param array $array
     * @return array 'm' => $maxValue, 'i' => $maxIndex
     */
    public static function arrayMaxWithIndex(array $array)
    {
        $maxValue = max($array);

        foreach ($array as $key => $value) {
            if ($value == $maxValue) $maxIndex = $key;
        }

        return array('m' => $maxValue, 'i'=> $maxIndex);
    }

}