<?php
namespace Phramework\URIStrategy;

use Phramework\API;
use Phramework\Exceptions\Permission;
use Phramework\Exceptions\NotFound;

/**
 * IURIStrategy implementation using default (old) class paths
 * @author Xenophon Spafaridis <nohponex@gmail.com>
 * @since 1.0.0
 */
class ClassBased implements IURIStrategy
{
    private $controller_whitelist;
    private $controller_unauthenticated_whitelist;
    private $controller_public_whitelist;
    private $namespace;
    private $suffix;

    public function __construct(
        $controller_whitelist,
        $controller_unauthenticated_whitelist,
        $controller_public_whitelist,
        $namespace,
        $suffix = ''
    ) {

        $this->controller_whitelist                 = $controller_whitelist;
        $this->controller_unauthenticated_whitelist = $controller_unauthenticated_whitelist;
        $this->controller_public_whitelist          = $controller_public_whitelist;
        $this->namespace                            = $namespace;
        $this->suffix                               = $suffix;
    }
    /**
     * [invoke description]
     * @param  [type] $requestMethod [description]
     * @param  [type] $params        [description]
     * @param  [type] $headers       [description]
     * @todo check request method
     * @return [type]                [description]
     */
    public function invoke($requestMethod, $requestParameters, $requestHeaders)
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
        $user       = API::getUser();

        //If not authenticated allow only certain controllers to access
        if (!$user &&
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
        $controller = $controller . $suffix;

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
