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

namespace Phramework\Authentication;

class Manager
{
    protected static $implementations = [];

    /**
     * Register an authentication implementation.
     * Implementation must implement
     * `\Phramework\Authentication\IAuthentication` interface
     * @param  string $implementation implementation class
     * @throws Exception
     */
    public static function register($implementation)
    {
        $object = new $implementation();
        if (!($object instanceof \Phramework\Authentication\IAuthentication)) {
            throw new \Exception(
                'Class is not implementing \Phramework\Authentication\IAuthentication'
            );
        }

        self::$implementations[] = $object;
    }

    /**
     * @return Phramework\Authentication\IAuthentication[]
     */
    public static function getImplementation()
    {
        return self::$implementations;
    }

    /**
     * Check user's authentication
     * This method iterates through all available authentication implementations
     * tests in priorioty order which of them might be provided and executes
     * @param  array  $params  Request parameters
     * @param  string $method  Request method
     * @param  array  $headers  Request headers
     * @return array|false Returns false on error or the user object on success
     * @throws Phramework\Exceptions\ServerException
     */
    public static function check($params, $method, $headers)
    {
        if (count(self::$implementations) !== 0 && !self::$userGetByEmailMethod) {
            throw new \Phramework\Exceptions\ServerException(
                'getUserByEmail method is not set'
            );
        }

        foreach (self::$implementations as $implementation) {
            if ($implementation->testProvidedMethod($params, $method, $headers)) {
                return $implementation->check($params, $method, $headers);
            }
        }

        return false; //Not found
    }

    /**
     * MUST be set
     * @var callable
     */
    protected static $userGetByEmailMethod = null;

    /**
     * @var string[]
     */
    protected static $attributes = [];

    /**
     * @var callable|null
     */
    protected static $onAuthenticateCallback = null;

    /**
     * @var callable|null
     */
    protected static $onCheckCallback = null;

    /**
     * Set the method that accepts email and returns a user object
     * MUST containg a password, id, this method MUST also contain any other
     * attribute specified in JWT::setAttributes method
     * @param callable $callable
     */
    public static function setUserGetByEmailMethod($callable)
    {
        if (!is_callable($callable)) {
            throw new \Exception('Provided method is not callable');
        }

        self::$userGetByEmailMethod = $callable;
    }

    /**
     * @return callable
     */
    public static function getUserGetByEmailMethod()
    {
        return self::$userGetByEmailMethod;
    }

    /**
     * Set attributes to be copied from user record.
     * Both `user_id` and `id` will use the user's id attribute
     * @param string[] $attributes
     */
    public static function setAttributes($attributes)
    {
        self::$attributes = $attributes;
    }

    /**
     * @return string[]
     */
    public static function getAttributes()
    {
        return self::$attributes;
    }

    /**
     * Set a callback that will be executed after a successful authenticate
     * execution, `user` object will be provided to the
     * defined callback.
     * @param callable $callable
     * @throws Exception
     */
    public static function setOnAuthenticateCallback($callable)
    {
        if (!is_callable($callable)) {
            throw new \Exception('Provided method is not callable');
        }

        self::$onAuthenticateCallback = $callable;
    }

    /**
     * @return callable
     */
    public static function getOnAuthenticateCallback()
    {
        return self::$onAuthenticateCallback;
    }

    /**
     * Set a callback that will be executed after a successful check
     * execution
     * @param callable $callable
     * @throws Exception
     */
    public static function setOnCheckCallback($callable)
    {
        if (!is_callable($callable)) {
            throw new \Exception('Provided method is not callable');
        }

        self::$onCheckCallback = $callable;
    }

    /**
     * @return callable
     */
    public static function getOnCheckCallback()
    {
        return self::$onCheckCallback;
    }
}
