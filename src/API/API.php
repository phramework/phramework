<?php

namespace Phramework\API;

use Phramework\API\models\util;
use Phramework\API\extensions\step_callback;

// Tell PHP that we're using UTF-8 strings until the end of the script
mb_internal_encoding('UTF-8');

// Tell PHP that we'll be outputting UTF-8 to the browser
mb_http_output('UTF-8');

//TODO remove
if(!function_exists('__')){
    function __($key){
        return API::get_translated($key);
    }
}
/**
 * API 'framework' by NohponeX
 * @license Proprietary This product is allowed only for usage by mathlogic.eu project 'dwaste atlas' and metaphrase
 * @todo Use parameters, implement alternative authentication methods
 * @todo Create a class for settings
 * @todo Clean GET callback
 * @todo Rething the role of $controller_public_whitelist
 * @author Xenophon Spafaridis <nohponex@gmail.com>
 * @link https://nohponex.gr Developer's website
 * @version 1.0.0
 * @package API
 * @todo remove APPPATH
 * @todo configurable APP\\controllers\\ namespace
 * @todo change default timezone
 * @todo remove _controller suffix
 * @todo Add localization class
 */
class API {
    protected static $instance;
    /**
     * Allowed controllers
     * @var array
     */
    private static $controller_whitelist;

    /**
     * Controllers that doesn't require authentication
     * @var array
     */
    private static $controller_unauthenticated_whitelist;

    /**
     * Controllers that doesn't require authentication
     * @var array
     */
    private static $controller_public_whitelist;
    private static $user;
    private static $language;
    private static $settings;
    private static $mode;
    /**
     * Viewer class
     */
    private static $viewer = 'Phramework\API\viewers\json';

    private static $controller;
    private static $method;

    /**
     * JSONP callback, When NULL no JSONP callback is set
     * @var type
     */
    private static $callback = NULL;

    /**
     * Exposed extensions
     */

    /**
     * step_callback extension
     * @var Phramework\API\extensions\step_callback
     */
    public $step_callback;
    /**
     * translation extension
     * @var Phramework\API\extensions\translation
     */
    public $translation;

    /**
     * Default mode
     */
    const MODE_DEFAULT = 'default';

    /**
     * Initialize API
     *
     * Only one instance of API may be present
     * @param array $settings
     * @param array $controller_whitelist
     * @param array $controller_unauthenticated_whitelist
     * @param array $controller_public_whitelist
     * @param string $mode [optional]
     * @param object|NULL $$translation_object [optional] Set custom translation class
     */
    public function __construct($settings, $controller_whitelist,
        $controller_unauthenticated_whitelist,
        $controller_public_whitelist,
        $mode = self::MODE_DEFAULT, $translation_object = NULL) {

        self::$settings = $settings;
        self::$controller_whitelist = $controller_whitelist;
        self::$controller_unauthenticated_whitelist = $controller_unauthenticated_whitelist;
        self::$controller_public_whitelist = $controller_public_whitelist;
        self::$mode = $mode;

        self::$user = FALSE;
        self::$language = 'en';

        //Instantiate step_callback object
        $this->step_callback = new \Phramework\API\extensions\step_callback();

        //If custom translation object is set add it
        if($translation_object){
            $this->set_translation_object($translation_object);
        }else{
            //Or instantiate default translation object
            $this->translation = new \Phramework\API\extensions\translation(
                self::get_setting('language'),
                self::get_setting('translation','track_missing_keys'));
        }

        self::$instance = $this;
    }

    public static function get_instance(){
        return self::$instance;
    }

    /**
     * Authentication class (Full namespace)
     */
    private static $authentication_class = 'Phramework\API\models\authentication';

    /**
     * Set authentication class
     * @param string $class A name of class that extends \Phramework\API\models\authentication
     */
    public static function set_authentication_class($class) {
        if (!is_subclass_of($class, 'Phramework\API\models\authentication', TRUE)) {
            throw new \Exception('class_is_not_implementing Phramework\API\models\authentication');
        }
        self::$authentication_class = $class;
    }

    /**
     * Authenticate a user
     *
     * The result will be stored at internal $user variable
     * @param type $username
     * @param type $password
     * @param array|FALSE Returns the user object
     */
    public static function authenticate($username, $password) {
        return call_user_func([self::$authentication_class, 'authenticate'], $username, $password);
    }

    public function set_translation_object($translation_object){
        if (!is_subclass_of($class, 'Phramework\API\extensions\translation', TRUE)) {
            throw new \Exception('class_is_not_implementing Phramework\API\extensions\translation');
        }
        $this->translation = $translation_object;
    }

    /**
     * Shortcut function alias of $this->translation->get_translated
     * @param type $key
     * @param type $parameters
     * @param type $fallback_value
     * @return type
     */
    public static function get_translated($key, $parameters = NULL, $fallback_value = NULL){
        return self::$instance->translation->get_translated($key, $parameters, $fallback_value);
    }
    /**
     * Execute the API
     * @throws exceptions\permission
     * @throws exceptions\not_found
     * @todo change default timezone
     * @todo change default language
     */
    public function invoke() {

        try {

            date_default_timezone_set('Europe/Athens');

            if (self::get_setting('debug')) {
                error_reporting(E_ALL);
                ini_set('display_errors', '1');
            }

            if(self::get_setting('errorlog_path')){
                ini_set('error_log', self::get_setting('errorlog_path'));
            }

            //Check if callback is set (JSONP)
            if (isset($_GET['callback'])) {
                if (!API\models\validate::is_valid_callback($_GET['callback'])) {
                    throw new exceptions\incorrect_paramenters(['callback']);
                }
                self::$callback = $_GET['callback'];
                unset($_GET['callback']);
            }

            //Initialize metaphrase\phpsdk\Metaphrase
            //$metaphrase = new \metaphrase\phpsdk\Metaphrase($settings['translate']['api_key']);
            //Initialize database connection if required or db set
            if (self::get_setting('require_db') || self::get_setting('db')) {
                models\database::require_database(self::get_setting('db'));
            }

            //Unset from memory database connection information
            unset(self::$settings['db']);

            //Allowed methods
            $method_whitelist = ['GET', 'POST', 'DELETE', 'PUT', 'HEAD', 'OPTIONS', 'PATCH'];

            //Get controller from the request (URL parameter)
            if (!isset($_GET['controller']) || empty($_GET['controller'])) {
                if (($default_controller = self::get_setting('default_controller'))) {
                    $_GET['controller'] = $default_controller;
                } else {
                    die(); //Or throw \Exception OR redirect to API documentation
                }
            }

            self::$controller = $controller = $_GET['controller'];
            unset($_GET['controller']);


            //Get method from the request (HTTP) method
            self::$method = $method =
                isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

            //Default value of response's header origin
            $origin = '*';

            //Get request headers
            $headers = util::headers();

            //Check origin header
            if (isset($headers['Origin'])) {
                $origin_host = parse_url($headers['Origin'], PHP_URL_HOST);
                //Check if origin host is allowed
                if ($origin_host && in_array($origin_host, self::get_setting('allowed_referer'))) {
                    $origin = $headers['Origin'];
                }
                //@TODO @security else deny access
            } elseif (isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI'])) {
                $origin = ''; //TODO Exctract origin from request url
            }
            if (!headers_sent()) {
                header('Access-Control-Allow-Credentials: true');
                header('Access-Control-Allow-Origin: ' . $origin);
                header('Access-Control-Allow-Methods: GET, POST, PUT, HEAD, DELETE, OPTIONS');
                header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, Content-Encoding');
            }
            //Catch OPTIONS request and kill it
            if ($method == 'OPTIONS') {
                header('HTTP/1.1 200 OK');
                exit();
            }

            //Authenticate request (check authentication)
            self::$user = call_user_func(array(self::$authentication_class, 'check'));

            //STEP_AFTER_AUTHENTICATION_CHECK
            $this->step_callback->call(step_callback::STEP_AFTER_AUTHENTICATION_CHECK);

            //Default language value
            $language = self::get_setting('language');

            //Select request's language
            if (isset($_GET['this_language']) && self::get_setting('languages') &&
                in_array($_GET['this_language'], self::get_setting('languages'))) { //Force requested language

                if ($_GET['this_language'] != $language) {
                    $language = $_GET['this_language'];
                }
                unset($_GET['this_language']);
            } else if (self::$user && isset(self::$user['language_code'])) { // Use user's langugae
                $language = self::$user['language_code'];
            } else if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && self::get_setting('languages')) { // Use Accept languge if provided
                $a = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

                if (in_array($a, self::get_setting('languages'))) {
                    $language = $a;
                }
            }

            //Set language variable
            self::$language = $language;
            $this->translation->set_language_code($language);

            //If not authenticated allow only certain controllers to access
            if (!self::get_user() &&
                !in_array($controller, self::$controller_unauthenticated_whitelist) &&
                !in_array($controller, self::$controller_public_whitelist)) {
                throw new exceptions\permission(API::get_translated('unauthenticated_access_exception'));
            }

            //Check if requested controller and method are allowed
            if (!in_array($controller, self::$controller_whitelist)) {
                throw new exceptions\not_found(API::get_translated('controller_not_found_exception'));
            } else if (!in_array($method, $method_whitelist)) {
                throw new exceptions\not_found(API::get_translated('method_not_found_exception'));
            }

            //STEP_BEFORE_REQUIRE_CONTROLLER
            $this->step_callback->call(step_callback::STEP_BEFORE_REQUIRE_CONTROLLER);

            //Include the requested controller file (containing the controller class)
            require(
                APPPATH . '/controllers/' .
                (self::$mode == self::MODE_DEFAULT ? '' : self::$mode . '/') .
                $controller . '.php');

            //Override method HEAD.
            // When HEAD method is called the GET method will be executed but no response boy will be send
            // we update the value of local variable $method sinse then original
            // requested method is stored at API::$method
            if ($method == 'HEAD') {
                $method = 'GET';
            }

            /**
             * Check if the requested controller and model is callable
             * In order to be callable :
             * 1) The controllers class must be defined as : myname_controller
             * 2) the methods must be defined as : public static function GET($params)
             *    where $params are the passed parameters
             */
            if (!is_callable(
                    "APP\\controllers" .
                    (self::$mode == self::MODE_DEFAULT ? '' : '\\' . self::$mode) .
                    "\\{$controller}_controller::$method")) {
                throw new exceptions\not_found('method_not_found_exception');
            }

            //Merge all REQUEST parameters into $params array
            $params = array_merge($_GET, $_POST, $_FILES); //TODO $_FILES only if POST OR PUT
            unset($_GET, $_POST, $_FILES);

            //Parse put or delete parameters into $params array
            if ($method == 'PUT' || $method == 'DELETE') {
                //Decode and merge params
                parse_str(file_get_contents('php://input'), $input);

                $params = array_merge($params, $input);
            }

            //STEP_BEFORE_CALL_METHOD
            $this->step_callback->call(step_callback::STEP_BEFORE_CALL_METHOD);

            //Call controller's method
            call_user_func([
                'APP\\controllers\\' .
                (self::$mode == self::MODE_DEFAULT ? '' : self::$mode . '\\') .
                $controller . '_controller', $method], $params);

            //Unset all
            unset($params);

            //STEP_BEFORE_CLOSE
            $this->step_callback->call(step_callback::STEP_BEFORE_CLOSE);

            //Try to close the databse
            models\database::close();
        } catch (exceptions\not_found $exception) {
            self::write_error_log(
                $exception->getMessage() .
                (isset($_SERVER['HTTP_REFERER']) ? ' from ' .
                    util::user_content($_SERVER['HTTP_REFERER']) : '')
            );
            self::error_view([
                'code' => $exception->getCode(),
                'error' => $exception->getMessage()
            ]);
        } catch (exceptions\request $exception) {

            self::error_view([
                'code' => $exception->getCode(),
                'error' => $exception->getMessage()]);
        } catch (exceptions\permission $exception) {
            self::write_error_log(
                $exception->getMessage());
            self::error_view(['code' => 403,
                'error' => $exception->getMessage(),
                'title' => 'permission_exception'
            ]);
        } catch (exceptions\missing_paramenters $exception) {
            self::write_error_log(
                $exception->getMessage() .
                implode(', ', $exception->getParameters())
            );

            if (self::get_setting('debug')) {
                self::error_view([
                    'code' => $exception->getCode(),
                    'error' => $exception->getMessage(),
                    'missing' => $exception->getParameters(),
                    'title' => 'missing_paramenters'
                ]);
            } else {
                self::error_view([
                    'code' => $exception->getCode(),
                    'error' => $exception->getMessage(),
                    'missing' => $exception->getParameters(),
                    'title' => 'missing_paramenters'
                ]);
            }
        } catch (exceptions\incorrect_paramenters $exception) {
            self::write_error_log(
                $exception->getMessage() . implode(', ', array_keys($exception->getParameters())));
            self::error_view([
                'code' => 400,
                'error' => $exception->getMessage() . ' : ' . implode(', ', array_keys($exception->getParameters())),
                'incorrect' => $exception->getParameters(),
                'title' => 'incorrect_parameters_exception'
            ]);
        } catch (exceptions\method_not_allowed $exception) {
            self::write_error_log(
                $exception->getMessage());
            
            //write allow header if AllowedMethods is set
            if (!headers_sent() && $exception->getAllowedMethods()) {
                header('Allow: ' . implode(', ', $exception->getAllowedMethods() ));
            }

            self::error_view([
                'code' => 400,
                'error' => $exception->getMessage() . ' : ' . implode(', ', array_keys($exception->getParameters())),
                'allow' => $exception->getAllowedMethods(),
                'title' => 'method_not_allowed'
            ]);
        } catch (\Exception $exception) {
            self::write_error_log(
                $exception->getMessage());
            self::error_view([
                'code' => 400,
                'error' => $exception->getMessage(),
                'title' => 'error'
            ]);
        }
    }

    /**
     * Get current user
     * @return array|FALSE Get current user's object
     */
    public static function get_user() {
        return self::$user;
    }

    /**
     * Get timezone offset in minutes
     *
     * This value is based on user's timezone
     * @todo Implement, get user's data
     * @return integer Returns offset from UTC in minutes
     */
    public static function get_timezone_offset() {
        return +2 * 60;
    }

    /**
     * Get current language
     * @return string Current language
     */
    public static function get_language() {
        return self::$language;
    }

    /**
     * Get requested method
     * @return string
     */
    public static function get_method() {
        return self::$method;
    }

    /**
     * Get requested controller
     * @return string
     */
    public static function get_controller() {
        return self::$controller;
    }

    /**
     * Get requested mode
     * @return string
     */
    public static function get_mode() {
        return self::$mode;
    }

    /**
     * Get current viewer
     * @return string
     */
    public static function get_viewer() {
        return self::$viewer;
    }

    /**
     * Get callback if set
     * @return string
     */
    public static function get_callback() {
        return self::$callback;
    }

    /**
     * Get a setting value
     * @param string $key The requested setting key
     * @param string|NULL $second_level
     * @param mixed $default_value [optional] Default value is the setting is missing.
     * @return Mixed Returns the value of setting, NULL when not found
     */
    public static function get_setting($key, $second_level = NULL, $default_value = NULL) {
        if (!isset(self::$settings[$key]) || ($second_level && isset(self::$settings[$key][$second_level]))) {
            return $default_value;
        }
        if($second_level) {
            self::$settings[$key][$second_level];
        }

        return self::$settings[$key];
    }

    /**
     * Set viewer class
     * @param string $class A name of class that implements \Phramework\API\viewers\IViewer
     */
    public static function set_viewer($class) {
        if (!is_subclass_of($class,'\Phramework\API\viewers\IViewer', TRUE)) {
            throw new \Exception('class_is_not_implementing Phramework\API\viewers\IViewer');
        }
        self::$viewer = $class;
    }

    /**
     * Output an error
     * @param array $params The error parameters. The 'error' index holds the message, and the 'code' message the error code, note that if headers are not send the response code will set with the 'code' value
     */
    private static function error_view($params) {
        if (!headers_sent()) {
            header('HTTP/1.0 ' . ( isset($params['code']) ? $params['code'] : '400' ));
        }
        self::view($params);
    }

    /**
     * Output the response using the selected viewer
     *
     * Multiple arguments can be set, first argument will always be used as the parameters array.
     * Custom IViewer implementation can use these additional parameters at they definition.
     * @param array $params The output parameters. Notice $params['user'] will be overwritten if set.
     * @param integer $status The response status
     * @return null Returns nothing
     */
    public static function view($parameters = []) {
        $args = func_get_args();

        //Access global user object
        $user = self::get_user();

        //Clean user object output
        $user = \Phramework\API\models\filter::out_entry($user, [
            'password', 'id']);

        /**
         * On HEAD method dont return response body, only the user's object
         */
        if (self::get_method() == 'HEAD') {
            $parameters = ['user' => $user];
        } else {
            //Merge output parameters with current user information, if any.
            $parameters = array_merge(['user' => $user], $parameters);
        }
        
        //Instanciate a new viewer object
        $viewer =  new self::$viewer();

        //rewrite $parameters to args
        $args[0] = $parameters;
        
        //Call view method
        return call_user_func_array([$viewer, 'view'], $args);
    }

    /**
     * Write a message to log file
     * @param String $message message to write
     * @todo improve
     */
    public static function write_error_log($message) {
        error_log(self::$mode . ',' . self::$method . ',' . self::$controller . ':' . $message);
    }


}
