<?php
namespace Phramework\URIStrategy;

use Phramework\API;
use Phramework\Exceptions\Permission;
use Phramework\Exceptions\NotFound;

/**
 * IURIStrategy Interface
 * @author Xenophon Spafaridis <nohponex@gmail.com>
 * @since 1.0.0
 */
class ClassBased implements IURIStrategy
{
    private $controller_whitelist;
    private $controller_unauthenticated_whitelist;
    private $controller_public_whitelist;
    private $namespace;

    public function __construct(
        $controller_whitelist,
        $controller_unauthenticated_whitelist,
        $controller_public_whitelist,
        $namespace
    ) {

        $this->controller_whitelist                 = $controller_whitelist;
        $this->controller_unauthenticated_whitelist = $controller_unauthenticated_whitelist;
        $this->controller_public_whitelist          = $controller_public_whitelist;
        $this->namespace                            = $namespace;
    }

    public function invoke($method, $params, $headers)
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
            throw new permission(API::getTranslated('unauthenticated_access_exception'));
        }

        //Check if requested controller and method are allowed
        if (!in_array($controller, $this->controller_whitelist)) {
            throw new not_found(API::getTranslated('controller_not_found_exception'));
        } elseif (!in_array($method, API::$method_whitelist)) {
            throw new not_found(API::getTranslated('method_not_found_exception'));
        }

        /**
         * Check if the requested controller and model is callable
         * In order to be callable :
         * 1) The controllers class must be defined as : myname_controller
         * 2) the methods must be defined as : public static function GET($params)
         *    where $params are the passed parameters
         */
        if (!is_callable($this->namespace . "{$controller}::$method")) {
            throw new not_found('method_not_found_exception');
        }

        //Call controller's method
        call_user_func(
            [$this->namespace . $controller, $method],
            $params
        );
    }
}
