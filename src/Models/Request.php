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

use Phramework\Exceptions\Source\ISource;
use Phramework\Exceptions\UnauthorizedException;
use \Phramework\Phramework;
use \Phramework\Exceptions\PermissionException;
use \Phramework\Exceptions\MissingParametersException;
use \Phramework\Exceptions\IncorrectParametersException;
use \Phramework\Validate\UnsignedIntegerValidator;
use \Phramework\Validate\Validate;

/**
 * Request related functions
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @since 0
 */
class Request
{
    const HEADER_CONTENT_TYPE = 'Content-Type';
    const HEADER_ACCEPT       = 'Accept';

    /**
     * Check if current request is authenticated
     *
     * Optionally it checks the authenticated user has a specific user_id
     * @param integer|null $userId *[Optional]* Check if current user has the same id with $userId
     * @return \stdClass Returns the user object
     * @throws \Phramework\Exceptions\PermissionException
     * @throws \Phramework\Exceptions\UnauthorizedException
     */
    public static function checkPermission($userId = null)
    {
        $user = \Phramework\Phramework::getUser();

        //If user is not authenticated throw an exception
        if (!$user) {
            throw new UnauthorizedException();
        }

        //Check if speficied user is same as current user
        if ($userId !== null && $user->id != $userId) {
            throw new PermissionException(
                'Insufficient permissions'
            );
        }

        return $user;
    }

    /**
     * Check if required parameters are set
     * @param array|object    $parameters Request's parameters
     * @param string|string[] $required The required parameters
     * @param ISource|null    $source
     * @throws \Phramework\Exceptions\MissingParametersException
     */
    public static function requireParameters(
        $parameters,
        $required,
        ISource $source = null
    ) {
        //Work with arrays
        if (is_object($parameters)) {
            $parameters = (array) $parameters;
        }

        $missing = [];

        if (!is_array($required)) {
            $required = [$required];
        }

        foreach ($required as $key) {
            if (!isset($parameters[$key])) {
                array_push($missing, $key);
            }
        }

        if (count($missing) > 0) {
            throw new MissingParametersException(
                $missing,
                $source
            );
        }
    }

    /**
     * Require id parameter if it's set else return NULL, it uses `resource_id` or `id` parameter if available
     * @param  array|object $parameters  The request parameters
     * @param  boolean      $UINTEGER  *[Optional]*, Check id's type to be unsigned integer
     * @throws \Phramework\Exceptions\IncorrectParameters When value is not correct
     * @return string|integer Returns the id or NULL if not set,
     * if $UINTEGER the returned value will be converted to unsigned integer
     * @deprecated since 2.0.0
     */
    public static function resourceId($parameters, $UINTEGER = true)
    {
        //Work with arrays
        if (is_object($parameters)) {
            $parameters = (array)$parameters;
        }

        //Check if is set AND validate
        if (isset($parameters['resource_id'])
            && preg_match(Validate::REGEXP_RESOURCE_ID, $parameters['resource_id']) !== false
        ) {
            if ($UINTEGER) {
                return UnsignedIntegerValidator::parseStatic($parameters['resource_id']);
            }
            return $parameters['resource_id'];
        }

        if (isset($parameters['id'])
            && preg_match(Validate::REGEXP_RESOURCE_ID, $parameters['id']) !== false
        ) {
            if ($UINTEGER) {
                return UnsignedIntegerValidator::parseStatic($parameters['id']);
            }
            return $parameters['id'];
        }
        return false;
    }

    /**
     * Require id parameter, it uses `resource_id` or `id` parameter if available
     * @param  array|object $parameters The request paramters
     * @param  boolean      $UINTEGER  *[Optional]*, Check id's type to be unsigned integer, default is true
     * @throws \Phramework\Exceptions\IncorrectParameters When value is not correct
     * @throws \Phramework\Exceptions\MissingParametersException When id is missing
     * if $UINTEGER the returned value will be converted to unsigned integer
     * @deprecated since 2.0.0
     */
    public static function requireId($parameters, $UINTEGER = true)
    {
        //Work with arrays
        if (is_object($parameters)) {
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
}
