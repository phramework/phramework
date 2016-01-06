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
namespace Phramework\URIStrategy;

use \Phramework\Phramework;
use \Phramework\Exceptions\PermissionException;
use \Phramework\Exceptions\NotFoundException;

/**
 * ClassBased strategy will use the controller parameters extracted from URI
 * and will attempt to include the respective class.
 *
 * Optionaly apache's configuration via .htaccess can convert the url from:
 *
 * `/?controller={controller}&resource_id={resource_id}` to `/{controller}/resource_id`
 *
 * and
 *
 * `/?controller={controller}` to `/{controller}`
 * ```
 * RewriteEngine On
 *
 * #Site full url with id
 * RewriteCond %{REQUEST_FILENAME} !-f
 * RewriteCond %{REQUEST_FILENAME} !-d
 * RewriteRule ^([a-z]{3,})/([a-zA-Z0-9_|%2520]+).*$ index.php?controller=$1&resource_id=$2 [L,QSA]

 * #Site full url
 * RewriteCond %{REQUEST_FILENAME} !-f
 * RewriteCond %{REQUEST_FILENAME} !-d
 * RewriteRule ^([a-z]{3,}).*$ index.php?controller=$1 [L,QSA]
 * ```
 * @todo document default_controller setting
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @since 1.0.0
 * @todo Add documentation for setting default_controller
 */
class ClassBased implements \Phramework\URIStrategy\IURIStrategy
{
    private $controllerWhitelist;
    private $controllerUnauthenticatedWhitelist;
    private $controllerPublicWhitelist;
    private $namespace;
    private $suffix;

    /**
     * Initialize ClassBased IURIStrategy
     * @param array $controllerWhitelist                Array with the allowed
     * controllers
     * @param array $controllerUnauthenticatedWhitelist Array with the
     * controllers which can be accessed without authentication
     * @param array $controllerPublicWhitelist          Array with the controllers
     * which can be accessed without authentication
     * @param string $namespace                         Controller's namespace
     * @param string $suffix                            [Optional] Default is NULL
     */
    public function __construct(
        $controllerWhitelist,
        $controllerUnauthenticatedWhitelist,
        $controllerPublicWhitelist,
        $namespace = '',
        $suffix = ''
    ) {
        $this->controllerWhitelist                  = $controllerWhitelist;
        $this->controllerUnauthenticatedWhitelist   = $controllerUnauthenticatedWhitelist;
        $this->controllerPublicWhitelist            = $controllerPublicWhitelist;
        $this->namespace                            = $namespace;
        $this->suffix                               = $suffix;
    }

    /**
     * Invoke URIStrategy
     * @param  object       $requestParameters Request parameters
     * @param  string       $requestMethod     HTTP request method
     * @param  array        $requestHeaders    Request headers
     * @param  object|false $requestUser       Use object if successful
     * authenticated otherwise false
     * @throws Phramework\Exceptions\NotFoundException
     * @throws Phramework\Exceptions\UnauthorizedException
     * @throws Phramework\Exceptions\ServerException
     * @return string[2] This method should return `[$class, $method]` on success
     */
    public function invoke(
        &$requestParameters,
        $requestMethod,
        $requestHeaders,
        $requestUser
    ) {
        //Get controller from the request (URL parameter)
        if (!isset($requestParameters['controller']) || empty($requestParameters['controller'])) {
            if (($defaultController = Phramework::getSetting('default_controller'))) {
                $requestParameters['controller'] = $defaultController;
            } else {
                throw new \Phramework\Exceptions\ServerException(
                    'Default controller has not been configured'
                );
            }
        }

        $controller = $requestParameters['controller'];
        unset($requestParameters['controller']);

        //Check if requested controller and method are allowed
        if (!in_array($controller, $this->controllerWhitelist)) {
            throw new NotFoundException('Method not found');
        } elseif (!in_array($requestMethod, Phramework::$methodWhitelist)) {
            throw new \Phramework\Exceptions\MethodNotAllowedException(
                'Method not found'
            );
        }

        //If not authenticated allow only certain controllers to access
        if (!$requestUser &&
            !in_array($controller, $this->controllerUnauthenticatedWhitelist) &&
            !in_array($controller, $this->controllerPublicWhitelist)) {
            throw new \Phramework\Exceptions\UnauthorizedException();
        }

        // Append suffix
        $controller = $controller . ($this->suffix ? $this->suffix : '');

        /**
         * Check if the requested controller and model is callable
         * In order to be callable :
         * 1) The controllers class must be defined as : myname_$suffix
         * 2) the methods must be defined as : public static function GET($requestParameters)
         *    where $requestParameters are the passed parameters
         */
        if (!is_callable($this->namespace . "{$controller}::$requestMethod")) {
            //Retry using capitalized first letter of the class
            $controller = ucfirst($controller);
            if (!is_callable($this->namespace . "{$controller}::$requestMethod")) {
                throw new NotFoundException('Method not found');
            }
        }

        //Call handler method
        call_user_func(
            [$this->namespace . $controller, $requestMethod],
            $requestParameters,
            $requestMethod,
            $requestHeaders
        );

        return [$controller, $requestMethod];
    }
}
