<?php

namespace Phramework\Models;

use Phramework\API;
use Phramework\Exceptions\Permission;
use Phramework\Exceptions\MissingParamenters;
use Phramework\Exceptions\IncorrectParameters;

/**
 * Request related functions
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @since 0
 * @package Phramework
 * @category Models
 */
class Request
{
    const HEADER_CONTENT_TYPE = 'Content-Type';
    const HEADER_ACCEPT       = 'Accept';

    /**
     * Check if current request is authenticated
     *
     * Optionaly it checks the authenticated user has a specific user_id
     * @param uint $user_id [optional] Check if current user has the same id with $user_id
     * @return array Returns the user object
     */
    public static function checkPermission($user_id = false)
    {
        $user = \Phramework\API::getUser();
        //If user is not authenticated throw an \Exception
        if (!$user) {
            throw new Permission(
                API::getTranslated('user_authentication_required_exception')
            );
        }

        //Check if speficied user is same as current user
        if ($user_id && $user['id'] != $user_id) {
            throw new Permission(
                API::getTranslated('insufficient_permissions_exception')
            );
        }
        return $user;
    }

    /**
     * Check if required parameters are set
     * @param Array @parameters Request's parameters
     * @param String|Array @ The required parameters
     * @return array Returns the values of required parameters
     */
    public static function requiredParameters($parameters, $required)
    {
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
            throw new MissingParamenters($missing);
        }

        return $return;
    }

    /**
     * Return resource id if it's set else return FALSE, use resource_id or id paramter if available
     * @param array $parameters  The request parameters
     * @param boolean $INTEGER  Check id's type to be unsigned integer
     * @throws MissingParamenters if id is set and not integer
     * @return string|integer Returns the id or FALSE if not set, it $INTGER the returned value will be converted to unsigned integer
     */
    public static function resourceId($parameters, $INTEGER = true)
    {
        //Check if is set AND validate
        if (isset($parameters['resource_id']) && preg_match(Validate::REGEXP_RESOURCE_ID, $parameters['resource_id']) !== false) {
            if ($INTEGER) {
                return Validate::uint($parameters['resource_id']);
            }
            return $parameters['resource_id'];
        }

        if (isset($parameters['id']) && preg_match(Validate::REGEXP_RESOURCE_ID, $parameters['id']) !== false) {
            if ($INTEGER) {
                return Validate::uint($parameters['id']);
            }
            return $parameters['id'];
        }
        return false;
    }

    /**
     * Require id paramter
     * @param $parameters Array The request paramters
     * @param $is_integer Boolean If TRUE validate the id as integer if FALSE as alphanumeric, Default TRUE
     * @throws MissingParamenters is not set
     * @throws IncorrectParameters if value is not correct
     * @return returns the value of the id parameter
     */
    public static function requiredId($parameters, $INTEGER = true)
    {
        if (isset($parameters['resource_id']) && preg_match(Validate::REGEXP_RESOURCE_ID, $parameters['resource_id']) !== false) {
            $parameters['id'] = $parameters['resource_id'];
        }
        if (!isset($parameters['id'])) {
            throw new MissingParamenters(['id']);
        }

        //Validate as unsigned integer
        if ($INTEGER && filter_var($parameters['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) === false) {
            throw new IncorrectParameters(['id']);
        } elseif (!$INTEGER && preg_match(Validate::REGEXP_RESOURCE_ID, $parameters['id']) === false) {
            //Validate as alphanumeric
            throw new IncorrectParameters(['id']);
        }
        return ($INTEGER ? intval($parameters['id']) : $parameters['id']);
    }

    /**
     * Required required values and parse provided parameters into an array
     * Validate the provided request model and return the
     * @uses \Phramework\Models\Request::requiredParameters
     * @uses \Phramework\Models\Validate::model
     * @param array $parameters
     * @param array $model
     * @return array Return the keys => values collection
     */
    public static function parseModel($parameters, $model)
    {
        $required_fields = [];
        foreach ($model as $key => $value) {
            if (in_array('required', $value, true) === true
                || in_array('required', $value, true) == true) {
                $required_fields[] = $key;
            }
        }

        \Phramework\Models\Request::requiredParameters($parameters, $required_fields);
        \Phramework\Models\Validate::model($parameters, $model);

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
