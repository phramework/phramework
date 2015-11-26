<?php
/**
 * Copyright 2015 Spafaridis Xenofon
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

use \Phramework\Phramework;
use \Phramework\Exceptions\PermissionException;
use \Phramework\Exceptions\MissingParametersException;
use \Phramework\Exceptions\IncorrectParametersException;

/**
 * Request related functions
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @since 0
 */
class Request
{
    const HEADER_CONTENT_TYPE = 'Content-Type';
    const HEADER_ACCEPT       = 'Accept';

    /**
     * Check if current request is authenticated
     *
     * Optionaly it checks the authenticated user has a specific user_id
     * @param int $user_id [optional] Check if current user has the same id with $user_id
     * @return array Returns the user object
     */
    public static function checkPermission($user_id = false)
    {
        $user = \Phramework\Phramework::getUser();
        //If user is not authenticated throw an \Exception
        if (!$user) {
            throw new \Phramework\Exceptions\UnauthorizedException();
        }

        //Check if speficied user is same as current user
        if ($user_id !== false && $user->id != $user_id) {
            throw new PermissionException(
                'Insufficient permissions'
            );
        }
        return $user;
    }

    /**
     * Check if required parameters are set
     * @param Array @parameters Request's parameters
     * @param String|Array @ The required parameters
     * @return array Returns the values of required parameters
     * @todo accept objects
     */
    public static function requireParameters($parameters, $required)
    {
        if (!is_array($parameters) && is_object($parameters)) {
            $parameters = (array)$parameters;
        }

        $missing = [];
        $return = [];
        if (!is_array($required)) {
            $required = [$required];
        }
        foreach ($required as $key) {
            if (!isset($parameters[$key])) {
                array_push($missing, $key);
            } else {
                $return[] = $parameters[$key];
            }
        }

        if (count($missing)) {
            throw new MissingParametersException($missing);
        }

        return $return;
    }

    /**
     * Require id parameter if it's set else return NULL, it uses `resource_id` or `id` parameter if available
     * @param array $parameters  The request parameters
     * @param boolean $UINTEGER  [Optional], Check id's type to be unsigned integer, default is true
     * @throws IncorrectParameters if value is not correct
     * @return string|int Returns the id or NULL if not set,
     * if $UINTEGER the returned value will be converted to unsigned integer
     * @todo accept objects
     */
    public static function resourceId($parameters, $UINTEGER = true)
    {
        if (!is_array($parameters) && is_object($parameters)) {
            $parameters = (array)$parameters;
        }

        //Check if is set AND validate
        if (isset($parameters['resource_id'])
            && preg_match(Validate::REGEXP_RESOURCE_ID, $parameters['resource_id']) !== false
        ) {
            if ($UINTEGER) {
                return Validate::uint($parameters['resource_id']);
            }
            return $parameters['resource_id'];
        }

        if (isset($parameters['id'])
            && preg_match(Validate::REGEXP_RESOURCE_ID, $parameters['id']) !== false
        ) {
            if ($UINTEGER) {
                return Validate::uint($parameters['id']);
            }
            return $parameters['id'];
        }
        return false;
    }

    /**
     * Require id parameter, it uses `resource_id` or `id` parameter if available
     * @param array $parameters The request paramters
     * @param boolean $UINTEGER  [Optional], Check id's type to be unsigned integer, default is true
     * @throws IncorrectParameters if value is not correct
     * if $UINTEGER the returned value will be converted to unsigned integer
     * @todo accept objects
     */
    public static function requireId($parameters, $UINTEGER = true)
    {
        if (!is_array($parameters) && is_object($parameters)) {
            $parameters = (array)$parameters;
        }

        if (isset($parameters['resource_id'])
            && preg_match(Validate::REGEXP_RESOURCE_ID, $parameters['resource_id']) !== false
        ) {
            $parameters['id'] = $parameters['resource_id'];
        }
        if (!isset($parameters['id'])) {
            throw new MissingParametersException(['id']);
        }

        //Validate as unsigned integer
        if ($UINTEGER
            && filter_var($parameters['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) === false
        ) {
            throw new IncorrectParametersException(['id']);
        } elseif (!$UINTEGER
            && preg_match(Validate::REGEXP_RESOURCE_ID, $parameters['id']) === false
        ) {
            //Validate as alphanumeric
            throw new IncorrectParametersException(['id']);
        }
        return ($UINTEGER ? intval($parameters['id']) : $parameters['id']);
    }

    /**
     * Required required values and parse provided parameters into an array
     * Validate the provided request model and return the
     * @uses \Phramework\Models\Request::requireParameters
     * @uses \Phramework\Validate\Validate::model
     * @param array $parameters
     * @param array $model
     * @return array Return the keys => values collection
     * @todo accept objects
     */
    public static function parseModel($parameters, $model)
    {
        if (!is_array($parameters) && is_object($parameters)) {
            $parameters = (array)$parameters;
        }

        $required_fields = [];
        foreach ($model as $key => $value) {
            if (in_array('required', $value, true) === true
                || in_array('required', $value, true) == true) {
                $required_fields[] = $key;
            }
        }

        Request::requireParameters($parameters, $required_fields);
        \Phramework\Validate\Validate::model($parameters, $model);

        $keys_values = [];
        foreach ($model as $key => $value) {
            if (isset($parameters[$key])) {
                if (in_array('nullable', $value) && $parameters[$key] == '0') {
                    $keys_values[$key] = null;
                    continue;
                }
                //Set value as null
                if (in_array('nullable', $value) && !$parameters[$key]) {
                    $keys_values[$key] = null;
                    continue;
                }
                /*
                if ($value['type'] == 'select' && !$parameters[$key]) {
                    $keys_values[$key] = NULL;
                } else {*/
                    $keys_values[$key] = $parameters[$key];
                /*}*/
            } elseif (($value['type'] == 'boolean' || in_array('boolean', $value))
                && (!isset($parameters[$key]) || !$parameters[$key])) {
                $keys_values[$key] = false;
            }
        }

        return $keys_values;
    }

    /**
     * Get the headers send with client's HTTP Request
     * @return array Return the array with the headers (indexes in lowercase)
     */
    public static function headers()
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$name] = $value;
            } elseif ($name == 'CONTENT_TYPE') {
                $headers['Content-Type'] = $value;
            } elseif ($name == 'CONTENT_LENGTH') {
                $headers['Content-Length'] = $value;
            }
        }
        return $headers;
    }

    /**
     * Merge put paramters into $parameters array
     * @param array $parameters Parameter's array
     */
    public static function mergePutParamters(&$parameters)
    {
        $put_parameters = json_decode(file_get_contents('php://input'), true);
        //Get params
        if (isset($put_params['params'])) {
            $parameters = array_merge($parameters, $put_parameters['params']);
        }
    }
}
