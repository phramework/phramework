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

use Phramework\API;
use Phramework\Models\Database;

/**
 * Authentication related functions
 *
 * Implements authentication using HTTP\s BASIC AUTHENTICATION
 * This class should be extended, this implementation will allways return false
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @since 0
 * @package Phramework
 * @category Models
 * @todo remove current implementation and provide only utilities fuctions, perhaps it should be an interface
 */
class Authentication
{
    /**
     * Check user's authentication, using data provided as BASIC AUTHENTICATION HEADERS
     * @todo Implement additional methods
     * @return array|FALSE Returns false on error or the user object on success
     */
    public static function check()
    {
        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
            return false;
        }

        //Validate authentication credentials
        \Phramework\Models\Validate::email($_SERVER['PHP_AUTH_USER']);

        $auth = self::authenticate($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);

        return $auth;
    }

    /**
     * Autheticate a user, using user's email and password
     * Always returns false
     * You must extend this class and implement this method
     * @param string $email
     * @param string $password
     * @return array|false Returns false on error or the user object on success
     * @throws \Phramework\Exceptions\Permission
     */
    public static function authenticate($email, $password)
    {
        return false;
    }
}
