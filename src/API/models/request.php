<?php

namespace Phramework\API\models;

use Phramework\API\API;
use Phramework\API\exceptions\permission;
use Phramework\API\exceptions\missing_paramenters;
use Phramework\API\exceptions\incorrect_paramenters;

/**
 * Request related functions
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @since 0
 * @package Phramework
 * @subpackage API
 * @category models
 */
class request {

    /**
     * Check if current request is authenticated
     *
     * Optionaly it checks the authenticated user has a specific user_id
     * @param string $user_type Optional, check user's type, Default value is USERGROUP_ANY
     * @param uint $user_id [optional] Check if current user has the same id with $user_id
     * @return array Returns the user object
     */
    public static function check_permission($user_id = FALSE) {
        $user = \Phramework\API\API::get_user();
        //If user is not authenticated throw an \Exception
        if (!$user) {
            throw new permission(
                API::get_translated('user_authentication_required_exception')
            );
        }

        //Check if speficied user is same as current user
        if ($user_id && $user['id'] != $user_id) {
            throw new permission(
                API::get_translated('insufficient_permissions_exception')
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
    public static function required_parameters($parameters, $required) {
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
            throw new missing_paramenters($missing);
        }

        return $return;
    }

    /**
     * Return resource id if it's set else return FALSE, use resource_id or id paramter if available
     * @param array $parameters  The request parameters
     * @param boolean $INTEGER  Check id's type to be unsigned integer
     * @throws missing_paramenters if id is set and not integer
     * @return string|integer Returns the id or FALSE if not set, it $INTGER the returned value will be converted to unsigned integer
     */
    public static function resource_id($parameters, $INTEGER = TRUE) {
        //Check if is set AND validate
        if (isset($parameters['resource_id']) && preg_match(validate::REGEXP_RESOURCE_ID, $parameters['resource_id']) !== FALSE) {
            if ($INTEGER) {
                return validate::uint($parameters['resource_id']);
            }
            return $parameters['resource_id'];
        }

        if (isset($parameters['id']) && preg_match(validate::REGEXP_RESOURCE_ID, $parameters['id']) !== FALSE) {
            if ($INTEGER) {
                return validate::uint($parameters['id']);
            }
            return $parameters['id'];
        }
        return FALSE;
    }

    /**
     * Require id paramter
     * @param $parameters Array The request paramters
     * @param $is_integer Boolean If TRUE validate the id as integer if FALSE as alphanumeric, Default TRUE
     * @throws missing_paramenters is not set
     * @throws incorrect_paramenters if value is not correct
     * @return returns the value of the id parameter
     */
    public static function required_id($parameters, $INTEGER = TRUE) {
        if (isset($parameters['resource_id']) && preg_match(validate::REGEXP_RESOURCE_ID, $parameters['resource_id']) !== FALSE) {
            $parameters['id'] = $parameters['resource_id'];
        }
        if (!isset($parameters['id'])) {
            throw new missing_paramenters([ 'id']);
        }

        //Validate as unsigned integer
        if ($INTEGER && filter_var($parameters['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) === FALSE) {
            throw new incorrect_paramenters(['id']);
        } elseif (!$INTEGER && preg_match(validate::REGEXP_RESOURCE_ID, $parameters['id']) === FALSE) { //Validate as alphanumeric
            throw new incorrect_paramenters([ 'id']);
        }
        return ( $INTEGER ? intval($parameters['id']) : $parameters['id'] );
    }

    /**
     * Required required values and parse provided parameters into an array
     * Validate the provided request model and return the
     * @uses \Phramework\API\models\request::required_parameters
     * @uses \Phramework\API\models\validate::model
     * @param array $parameters
     * @param array $model
     * @return array Return the keys => values collection
     */
    public static function parse_model($parameters, $model) {
        $required_fields = [];
        foreach ($model as $key => $value) {
            if (in_array('required', $value, TRUE) === TRUE
                || in_array('required', $value, TRUE) == TRUE ) {
                $required_fields[] = $key;
            }
        }

        \Phramework\API\models\request::required_parameters($parameters, $required_fields);
        \Phramework\API\models\validate::model($parameters, $model);

        $keys_values = [];
        foreach ($model as $key => $value) {
            if (isset($parameters[$key])) {
                if (in_array('nullable', $value) && $parameters[$key] == '0') {
                    $keys_values[$key] = NULL;
                    continue;
                }
                //Set value as null
                if (in_array('nullable', $value) && !$parameters[$key]) {
                    $keys_values[$key] = NULL;
                    continue;
                }
                /*
                if ($value['type'] == 'select' && !$parameters[$key]) {
                    $keys_values[$key] = NULL;
                } else {*/
                    $keys_values[$key] = $parameters[$key];
                /*}*/
            } elseif (
                ($value['type'] == 'boolean' || in_array('boolean', $value))
                && (!isset($parameters[$key]) || !$parameters[$key])) {
                $keys_values[$key] = FALSE;
            }
        }

        return $keys_values;
    }
}
