<?php
/**
 * Copyright 2015 Xenofon Spafaridis
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
 * @since 0
 */
class Filter
{
    /**
     * Allow only keys of $whitelist in every row of $data array
     *
     * @param array $data
     * @param array $whitelist
     * @return array
     */
    public static function in($data, $whitelist)
    {
        if ($data) {
            if (is_object($data)) {
                $data = get_object_vars($data);
            }
            $whitelist = array_flip($whitelist);

            //Filter
            foreach ($data as $key => $value) {
                if (is_object($value)) {
                    $value = get_object_vars($value);
                }
                $data[$key] = array_intersect_key($value, $whitelist);
            }
        }
        return $data;
    }

    /**
     * Allow only keys of $whitelist in object
     *
     * @param array $data
     * @param array $whitelist
     * @return array
     */
    public static function inEntry($data, $whitelist)
    {
        if ($data) {
            $whitelist = array_flip($whitelist);

            if (is_object($data)) {
                $data = get_object_vars($data);
            }

            $data = array_intersect_key($data, $whitelist);
        }
        return $data;
    }

    /**
     * Exclude blackisted keys of $blacklist in every row of $data array
     *
     * @param array $data
     * @param array $blacklist
     * @return array
     */
    public static function out($data, $blacklist)
    {
        if ($data) {
            $blacklist = array_flip($blacklist);

            //Filter
            foreach ($data as $key => $value) {
                $data[$key] = array_diff_key($value, $blacklist);
            }
        }
        return $data;
    }

    /**
     * Exclude blackisted keys of $blacklist in $data object
     *
     * @param array $data
     * @param array $blacklist
     * @return array
     */
    public static function outEntry($data, $blacklist)
    {
        if ($data) {
            $blacklist = array_flip($blacklist);

            $data = array_diff_key($data, $blacklist);
        }
        return $data;
    }

    /**
     * Filter string, applies FILTER_SANITIZE_STRING
     * @param string $value Input string
     * @param integer|NULL $max_length Max length of the string, optional. Default value is NULL (no limit)
     * @return string Returns the filtered string
     */
    public static function string($value, $max_length = null)
    {
        /*if (!is_string($value)) {
            throw new \Exception('not_a_string');
        }*/
        $value = filter_var(trim($value), FILTER_SANITIZE_STRING);

        if ($max_length && mb_strlen($value) > $max_length) {
            $value = mb_substr($value, 0, $max_length);
        }
        return $value;
    }

    /**
     * Filter email
     * @param string $value
     * @return string Returns the filtered email
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
     */
    public static function boolean($value)
    {
        if ($value && strtolower($value) != 'false') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Typecast a value
     * @param mixed $value
     * @param string $type
     * @return mixed The typecasted value
     * @deprecated since 1.1.0
     */
    public static function typecast(&$value, $type)
    {
        switch ($type) {
            case Validate::TYPE_INT:
            case Validate::TYPE_UINT:
                $value = intval($value);
                break;
            case Validate::TYPE_FLOAT:
                $value = floatval($value);
                break;
            case Validate::TYPE_DOUBLE:
                $value = doubleval($value);
                break;
            case Validate::TYPE_BOOLEAN:
                $value = boolval($value);
                break;
            case Validate::TYPE_UNIX_TIMESTAMP:
                //Add the timezone offset (in minutes)
                $value = intval($value) +
                    (\Phramework\Phramework::getTimezoneOffset()*60);
                break;
        }
    }

    /**
     * Type cast entry's attributs based on the provided model
     *
     * If any TYPE_UNIX_TIMESTAMP are present an additional attribute will
     * be included with the suffix _formatted, the format of the string can be
     * changed from timestamp_format setting.
     * @param array $entry
     * @param array $model
     * @return array Returns the typecasted entry
     * @deprecated since 1.1.0
     */
    public static function castEntry($entry, $model)
    {
        if (!$entry) {
            return $entry;
        }

        $timestamp_format = \Phramework\Phramework::getSetting(
            'timestamp_format',
            null,
            'Y-m-d\TH:i:s\Z'
        );

        //Repeat for each model's attribute of the entry.
        //$k holds the key of the attribute and $v the type
        foreach ($model as $k => $v) {
            if (!isset($entry[$k])) {
                continue;
            }

            //Typecast
            Filter::typecast($entry[$k], $v);

            //if type is a Validate::TYPE_UNIX_TIMESTAMP
            //then inject a string version of the timestamp to this entry
            if ($v === Validate::TYPE_UNIX_TIMESTAMP) {
                //offset included!
                $converted = gmdate($timestamp_format, $entry[$k]);

                //inject the string version of the timestamp
                $entry[$k . '_formatted'] = $converted;
            }
        }

        return $entry;
    }

    /**
     * Type cast each entry of list based on the provided model
     * @param array $list
     * @param array $model
     * @return array Returns the typecasted list
     * @deprecated since 1.1.0
     */
    public static function cast($list, $model)
    {
        if (!$list) {
            return $list;
        }

        //Apply cast entry to each entry
        foreach ($list as $k => &$v) {
            $v= self::castEntry($v, $model);
        }

        return $list;
    }
}
