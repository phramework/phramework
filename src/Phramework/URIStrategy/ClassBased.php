<?php
namespace Phramework\URIStrategy;

use Phramework\API;
use Phramework\Exceptions\Permission;
use Phramework\Exceptions\NotFound;

/**
 * IURIStrategy implementation using default (old) class paths
 * @todo document default_controller setting
 * @author Xenophon Spafaridis <nohponex@gmail.com>
 * @since 1.0.0
 */
class ClassBased implements IURIStrategy
{
    private $controllerWhitelist;
    private $controllerUnauthenticatedWhitelist;
    private $controllerPublicWhitelist;
    private $namespace;
    private $suffix;

    /**
     * [__construct description]
     * @param Array $controllerWhitelist                Array with the allowed controllers
     * @param Array $controllerUnauthenticatedWhitelist Array with the controllers which can be accessed without authentication
     * @param Array $controllerPublicWhitelist          Array with the controllers which can be accessed without authentication
     * @param String $namespace                         Controller's namespace
     * @param String $suffix                            [Optional] Default is NULL
     */
    public function __construct(
        $controllerWhitelist,
        $controllerUnauthenticatedWhitelist,
        $controllerPublicWhitelist,
        $namespace,
        $suffix = ''
    ) {

        $this->controller_whitelist                 = $controllerWhitelist;
        $this->controller_unauthenticated_whitelist = $controllerUnauthenticatedWhitelist;
        $this->controller_public_whitelist          = $controllerPublicWhitelist;
        $this->namespace                            = $namespace;
        $this->suffix                               = $suffix;
    }
    /**
     * [invoke description]
     * @param  String $requestMethod [description]
     * @param  Array $requestParameters        [description]
     * @param  Array $requestHeaders       [description]
     * @todo check request method
     * @return Boolean                [description]
     */
    public function invoke($requestMethod, $requestParameters, $requestHeaders, $requestUser)
    {

        //Get controller from the request (URL parameter)
        if (!isset($params['controller']) || empty($params['controller'])) {
            if (($default_controller = API::getSetting('default_controller'))) {
                $params['controller'] = $default_controller;
            } else {
                die(); //Or throw \Exception OR redirect to API documentation
            }
        }

        $controller = $params['controller'];
        unset($params['controller']);

        //If not authenticated allow only certain controllers to access
        if (!$requestUser &&
            !in_array($controller, $this->controller_unauthenticated_whitelist) &&
            !in_array($controller, $this->controller_public_whitelist)) {
            throw new Permission(API::getTranslated('unauthenticated_access_exception'));
        }

        //Check if requested controller and method are allowed
        if (!in_array($controller, $this->controller_whitelist)) {
            throw new NotFound(API::getTranslated('controller_NotFound_exception'));
        } elseif (!in_array($requestMethod, API::$methodWhitelist)) {
            throw new NotFound(API::getTranslated('method_NotFound_exception'));
        }

        // Append suffix
        $controller = $controller . ($suffix ? $suffix : '');

        /**
         * Check if the requested controller and model is callable
         * In order to be callable :
         * 1) The controllers class must be defined as : myname_$suffix
         * 2) the methods must be defined as : public static function GET($params)
         *    where $params are the passed parameters
         */
        if (!is_callable($this->namespace . "{$controller}::$requestMethod")) {
            throw new NotFound(API::getTranslated('method_NotFound_exception'));
        }

        //Call controller's method
        call_user_func(
            [$this->namespace . $controller, $requestMethod],
            $requestParameters,
            $requestMethod,
            $requestHeaders
        );
    }
}
