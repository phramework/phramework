<?php
/**
 * Copyright 2015-2016 Xenofon Spafaridis
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Phramework\Models;

use \Phramework\Validate\Validate;

/**
 * Provides various methods for filtering data
 *
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @since 0.0.0
 */
class Filter
{
    /**
     * Allow only keys of $whiteList in every row of $data array
     *
     * @param array $data
     * @param array $whiteList
     * @return array
     */
    public static function in($data, $whiteList)
    {
        if ($data) {
            if (is_object($data)) {
                $data = get_object_vars($data);
            }
            $whiteList = array_flip($whiteList);

            //Filter
            foreach ($data as $key => $value) {
                if (is_object($value)) {
                    $value = get_object_vars($value);
                }
                $data[$key] = array_intersect_key($value, $whiteList);
            }
        }
        return $data;
    }

    /**
     * Allow only keys of $whiteList in object
     *
     * @param array $data
     * @param array $whiteList
     * @return array
     */
    public static function inEntry($data, $whiteList)
    {
        if ($data) {
            $whiteList = array_flip($whiteList);

            if (is_object($data)) {
                $data = get_object_vars($data);
            }

            $data = array_intersect_key($data, $whiteList);
        }
        return $data;
    }

    /**
     * Exclude blackisted keys of $blackList in every row of $data array
     *
     * @param array $data
     * @param array $blackList
     * @return array
     */
    public static function out($data, $blackList)
    {
        if ($data) {
            $blackList = array_flip($blackList);

            //Filter
            foreach ($data as $key => $value) {
                $data[$key] = array_diff_key($value, $blackList);
            }
        }
        return $data;
    }

    /**
     * Exclude blacklisted keys of $blackList in $data object
     *
     * @param array $data
     * @param array $blackList
     * @return array
     */
    public static function outEntry($data, $blackList)
    {
        if ($data) {
            $blackList = array_flip($blackList);

            $data = array_diff_key($data, $blackList);
        }
        return $data;
    }

    /**
     * Filter string, applies FILTER_SANITIZE_STRING
     * @param string $value Input string
     * @param integer|NULL $maxLength Maximum length of the string, optional. Default value is null (no limit)
     * @return string Returns the filtered string
     * @todo use Validate library
     */
    public static function string($value, $maxLength = null)
    {
        /*if (!is_string($value)) {
            throw new \Exception('not_a_string');
        }*/
        $value = filter_var(trim($value), FILTER_SANITIZE_STRING);

        if ($maxLength && mb_strlen($value) > $maxLength) {
            $value = mb_substr($value, 0, $maxLength);
        }
        return $value;
    }

    /**
     * Filter email
     * @param string $value
     * @return string Returns the filtered email
     * @todo use Validate library
     */
    public static function email($value)
    {
        return filter_var(trim($value), FILTER_VALIDATE_EMAIL);
    }

    /**
     * Parse input value as boolean
     *
     * @param string|boolean $value Input value
     * @return boolean Return the input value as boolean
     * @todo use Validate library
     */
    public static function boolean($value)
    {
        if ($value && strtolower($value) != 'false') {
            return true;
        } else {
            return false;
        }
    }
}
