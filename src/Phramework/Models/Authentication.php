<?php

namespace Phramework\Models;

use Phramework\API;
use Phramework\Models\Database;

/**
 * Authentication related functions
 *
 * Implements authentication using HTTP\s BASIC AUTHENTICATION
 * This class should be extended if Database structure differs
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @since 0
 * @package Phramework
 * @subpackage API
 * @category models
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
