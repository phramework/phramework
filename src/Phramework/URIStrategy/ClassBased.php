<?php
namespace Phramework\URIStrategy;

use Phramework\API;
use Phramework\Exceptions\Permission;
use Phramework\Exceptions\NotFound;

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
 * @author Xenophon Spafaridis <nohponex@gmail.com>
 * @since 1.0.0
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

        $this->controller_whitelist                   = $controllerWhitelist;
        $this->controller_unauthenticated_whitelist = $controllerUnauthenticatedWhitelist;
        $this->controller_public_whitelist           = $controllerPublicWhitelist;
        $this->namespace                               = $namespace;
        $this->suffix                                  = $suffix;
    }

    public function invoke($requestMethod, $requestParameters, $requestHeaders, $requestUser)
    {
        //Get controller from the request (URL parameter)
        if (!isset($requestParameters['controller']) || empty($requestParameters['controller'])) {
            if (($default_controller = API::getSetting('default_controller'))) {
                $requestParameters['controller'] = $default_controller;
            } else {
                die(); //Or throw \Exception OR redirect to API documentation
            }
        }

        $controller = $requestParameters['controller'];
        unset($requestParameters['controller']);

        //Check if requested controller and method are allowed
        if (!in_array($controller, $this->controller_whitelist)) {
            throw new NotFound(API::getTranslated('controller_NotFound_exception'));
        } elseif (!in_array($requestMethod, API::$methodWhitelist)) {
            throw new NotFound(API::getTranslated('method_NotFound_exception'));
        }

        //If not authenticated allow only certain controllers to access
        if (!$requestUser &&
            !in_array($controller, $this->controller_unauthenticated_whitelist) &&
            !in_array($controller, $this->controller_public_whitelist)) {
            throw new Permission(API::getTranslated('unauthenticated_access_exception'));
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
                throw new NotFound(API::getTranslated('method_NotFound_exception'));
            }
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
