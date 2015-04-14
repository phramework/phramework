<?php

namespace Phramework\API\models;

use Phramework\API\models\database;

/**
 * Authentication related functions
 * 
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @since 0
 * @package Phramework
 * @subpackage API
 * @category models
 */
class authentication {

    /**
     * Check user's authentication, using data provided as BASIC AUTHENTICATION HEADERS
     * @todo Implement additional methods
     * @return array|FALSE Returns false on error or the user object on success
     */
    public static function check() {
        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
            return FALSE;
        }
        
        
        //Validate authentication credentials
        \Phramework\API\models\validate::email($_SERVER['PHP_AUTH_USER']);

        $auth = self::authenticate($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);

        return $auth;
    }

    /**
     * Autheticate a user, using user's email and password
     * @todo Implement validate
     * @param string $email
     * @param string $password
     * @return array|FALSE Returns false on error or the user object on success
     * @throws \API\exceptions\permission
     */
    public static function authenticate($email, $password) {

        //Select using user's email       
        $auth = database::ExecuteAndFetch('SELECT "id", "username", "email", "password", "language_code", "usergroup", "disabled"'
              .'FROM "user" WHERE LOWER("email") = ?  LIMIT 1', [strtolower($email)]);

        //Check if user exists        
        if (!$auth) {
            return FALSE;
        }
        //Check if user is disabled
        if ($auth['disabled']) {
            throw new \Phramework\API\exceptions\permission(__('disabled_account_exception'));
        }
        
        //Check if user is validated
        /* if( !$auth[ 'validated' ] ) {
          //TODO @security @validation
          } */

        //Verify password hash
        if (password_verify($password, $auth['password'])) {
            //Force corrent types 
            $auth['id'] = intval($auth['id']);

            //Return without the password field
            return \Phramework\API\models\filter::out_entry($auth, [ 'password', 'disabled', 'validated']);
        } else {
            //In case of incorrect password
            return FALSE;
        }
    }
}