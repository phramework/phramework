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

namespace Phramework\Authentication;

/**
 * Authentication related functions
 *
 * Implements authentication using HTTP\s BASIC AUTHENTICATION
 * This class should be extended, this implementation will allways return false
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @since 1
 */
interface IAuthentication
{
    /**
     * Check user's authentication
     * @param  array  $params  Request parameters
     * @param  string $method  Request method
     * @param  array  $headers Request headers
     * @return array|false Returns false on error or the user object on success
     */
    public function check($params, $method, $headers);

    /**
     * Autheticate a user, using user's email and password
     * Always returns false
     * You must extend this class and implement this method
     * @param  array  $params  Request parameters
     * @param  string $method  Request method
     * @param  array  $headers Request headers
     * @return array|false Returns false on error or the user object on success
     * @throws Phramework\Exceptions\PermissionException
     */
    public function authenticate($params, $method, $headers);

    /**
     * Test if current request holds authoratation data
     * @param  array  $params  Request parameters
     * @param  string $method  Request method
     * @param  array  $headers  Request headers
     * @return boolean
     */
    public function testProvidedMethod($params, $method, $headers);
}
