<?php

namespace Phramework\API;

use Phramework\API\models\authentication;
use Phramework\API\models\util;

/**
 * index.php
 * This is the main file of the API SERVER.
 * It implements an MVC architectural pattern, where viewer is a JSON viewer or a raw binary file output.
 */

/**
 * Define APIPATH
 */
define('APIPATH', __DIR__);

// Tell PHP that we're using UTF-8 strings until the end of the script
mb_internal_encoding('UTF-8');

// Tell PHP that we'll be outputting UTF-8 to the browser
mb_http_output('UTF-8');

/**
 * API 'framework' by NohponeX
 * @license Proprietary This product is allowed only for usage by mathlogic.eu project 'dwaste atlas'
 * @todo Use parameters, implement alternative authentication methods
 * @todo Create a class for settings
 * @todo Clean GET callback
 * @todo Rething the role of $controller_public_whitelist
 * @author NohponeX, nohponex@gmail.com
 * @link https://nohponex.gr Developer's website
 * @link http://mathlogic.eu
 * @version 0.1.1
 * @package API
 */
class API {

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

    private static $controller;
    private static $method;

    /**
     * JSONP callback
     * @var type
     */
    private static $callback = NULL;

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
     */
    public function __construct($settings, $controller_whitelist, $controller_unauthenticated_whitelist, $controller_public_whitelist, $mode = self::MODE_DEFAULT) {

        self::$settings = $settings;
        self::$controller_whitelist = $controller_whitelist;
        self::$controller_unauthenticated_whitelist = $controller_unauthenticated_whitelist;
        self::$controller_public_whitelist = $controller_public_whitelist;
        self::$mode = $mode;

        self::$user = FALSE;
        self::$language = 'en';
    }

    private static $authentication_class = 'Phramework\API\models\authentication';

    /**
     * Set authentication class
     * @param string $class A name of class that extends \Phramework\API\models\authentication
     */
    public static function set_authentication_class($class) {
        if(!is_subclass_of($class, 'Phramework\API\models\authentication', TRUE)) {
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
        //return self::$authentication_class::authenticate::authenticate($username, $password);

        return call_user_func(array(self::$authentication_class, 'authenticate'), $username, $password);

        //return self::$user = authentication::authenticate($username, $password);
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

            ini_set('error_log', self::get_setting('errorlog_path'));

            //Check if callback is set (JSONP)
            if(isset($_GET['callback'])) {
                if(!API\models\validate::is_valid_callback($_GET['callback'])) {
                    throw new exceptions\incorrect_paramenters(['callback']);
                }
                self::$callback=$_GET['callback'];
                unset($_GET['callback']);
            }

            //Initialize metaphrase\phpsdk\Metaphrase
            //$metaphrase = new \metaphrase\phpsdk\Metaphrase($settings['translate']['api_key']);
            //
            //Initialize database connection
            models\database::require_database(self::get_setting('db'));
            
            //Unset from memory database connection information
            unset(self::$settings['db']);

            //Allowed methods
            $method_whitelist = ['GET', 'POST', 'DELETE', 'PUT', 'HEAD', 'OPTIONS'];

            //Get controller from the request ( URL parameter )
            if (!isset($_GET['controller'])) {
                die(); //Or throw exception OR redirect to API documentation
            }
            self::$controller = $controller = $_GET['controller'];
            unset($_GET['controller']);

            //Get method from the request (HTTP) method
            self::$method = $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

            //Cross origin feature
            $origin = '*';

            $headers = util::headers();

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

            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, Content-Encoding');

            //Catch OPTIONS request and kill it
            if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
                header('HTTP/1.1 200 OK');
                exit();
            }

            //If a request from site ( using HTML request referer )
            $request_from_site = FALSE;

            //Check if the request is comming from web
            if (isset($_SERVER['HTTP_REFERER'])) {
                $referer_data = parse_url($_SERVER['HTTP_REFERER']);
                if (in_array($referer_data['host'], self::get_setting('allowed_referer'))) {
                    $request_from_site = TRUE;
                }
            }

            //Authenticate request
            self::$user = call_user_func(array(self::$authentication_class, 'check'));

            //STEP_AFTER_AUTHENTICATION_CHECK
            self::call_callback(self::STEP_AFTER_AUTHENTICATION_CHECK);

            //@todo update
            $language = self::get_setting('language');

            //Select request's language
            if (isset($_GET['this_language']) && in_array($_GET['this_language'], self::get_setting('languages'))) { //Force requested language
                if ($_GET['this_language'] != $language) {
                    $language = $_GET['this_language'];
                }
                unset($_GET['this_language']);
            } else if (self::$user) { // Use user's langugae
                $language = self::$user['language_code'];
            } else if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) { // Use Accept languge if provided
                $a = str_replace('el', 'gr', substr(strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']), 0, 2));

                if (in_array($a, self::get_setting('languages'))) {
                    $language = $a;
                } else {
                    //Force english for not greek & english browsers
                    $language = self::get_setting('language');
                }
            }

            self::$language = $language;

            /*
             * Load language file
             * each langauge file should have a global $strings variable
             */
            //require( "../language/$language.php" );

            //If not authenticated allow only certain controllers to access
            if (!self::get_user() && !in_array($controller, self::$controller_unauthenticated_whitelist) && !in_array($controller, self::$controller_public_whitelist)) {
                throw new exceptions\permission(\__('unauthenticated_access_exception'));
            }

            //Check if requested controller and method are allowed
            if (!in_array($controller, self::$controller_whitelist)) {
                throw new exceptions\not_found(__('controller_not_found_exception'));
            } else if (!in_array($method, $method_whitelist)) {
                throw new exceptions\not_found(__('method_not_found_exception'));
            }

            //STEP_BEFORE_REQUIRE_CONTROLLER
            self::call_callback(self::STEP_BEFORE_REQUIRE_CONTROLLER);

            //Include the requested controller file (containing the controller class)
            require(APPPATH . '/controllers/' . (self::$mode == self::MODE_DEFAULT ? '' : self::$mode.'/') . $controller . '.php');

            /**
             * Check if the requested controller and model is callable
             * In order to be callable :
             * 1) The controllers class must be defined as : myname_controller
             * 2) the methods must be defined as : public static function GET($params)
             *    where $params are the passed parameters
             */
            if (!is_callable("APP\\controllers" . (self::$mode == self::MODE_DEFAULT ? '' : '\\' . self::$mode) . "\\{$controller}_controller::$method")) {
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
            self::call_callback(self::STEP_BEFORE_CALL_METHOD);

            //Call controller's method
            call_user_func([ 'APP\\controllers\\' . (self::$mode == self::MODE_DEFAULT ? '' : self::$mode . '\\') . $controller . '_controller', $method], $params);

            //Unset all
            unset($params);

            //STEP_BEFORE_CLOSE
            self::call_callback(self::STEP_BEFORE_CLOSE);

            //Try to close the databse
            models\database::close();
        } catch (exceptions\not_found $exception) {
            self::write_error_log($exception->getMessage() . ( isset($_SERVER['HTTP_REFERER']) ? ' from ' . util::user_content($_SERVER['HTTP_REFERER']) : '' ));
            self::error_view([ 'code' => $exception->getCode(), 'error' => $exception->getMessage()]);
        } catch (exceptions\request $exception) {

            self::error_view([ 'code' => $exception->getCode(), 'error' => $exception->getMessage()]);
        } catch (exceptions\permission $exception) {
            self::write_error_log($exception->getMessage());
            self::error_view([ 'code' => 403, 'error' => $exception->getMessage(), 'title' => 'error']);
        } catch (exceptions\missing_paramenters $exception) {
            self::write_error_log($exception->getMessage() . implode(', ', $exception->getParameters()));
            if (self::get_setting('debug')) {
                self::error_view([ 'code' => $exception->getCode(), 'error' => $exception->getMessage(), 'missing' => $exception->getParameters()]);
            } else {
                self::error_view([ 'code' => $exception->getCode(), 'error' => $exception->getMessage()]);
            }
        } catch (exceptions\incorrect_paramenters $exception) {
            self::write_error_log($exception->getMessage() . implode(', ', $exception->getParameters()));
            self::error_view([ 'code' => 400, 'error' => $exception->getMessage() . ' : ' . implode(', ', $exception->getParameters()), 'incorrect' => $exception->getParameters(), 'title' => 'incorrect_parameters_exception']);
        } catch (\Exception $exception) {
            self::write_error_log($exception->getMessage());
            self::error_view([ 'code' => 400, 'error' => $exception->getMessage(), 'title' => 'Error']);
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
       return +2*60;
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
     * Get a setting value
     * @param string $key The requested setting key
     * @return Mixed Returns the value of setting, NULL when not found
     */
    public static function get_setting($key) {
        if (!isset(self::$settings[$key])) {
            return NULL;
            //throw new Exception('Not a valid setting key');
        }

        return self::$settings[$key];
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
     * Output the response in json encoded format.
     * @param array $params The output parameters. Notice $params[ 'user' ] will be overwritten if set.
     * @todo Test JSONP
     * @return null Returns nothing
     */
    public static function view($params = []) {
        //Access global user variable
        $user = self::get_user();

        //Clean user object output
        $user = \Phramework\API\models\filter::out_entry($user, ['password', 'id']);

        //Merge output parameters with current user information, if any.
        $params = array_merge([ 'user' => $user], $params);
        header('Content-type: application/json;charset=utf-8');

        //If JSONP requested (if callback is requested though GET)
        if(self::$callback) {
            echo $callback;
            echo '([';
            echo json_encode($params);
            echo '])';
        }else{
            echo json_encode($params);
        }

    }

    /**
     * Include an php file ( Used instead of include_once method )
     * @param string @path File to include
     * @deprecated since version 0 not safe
     */
    public static function clude($path) {
        static $included = [];

        //If not included
        if (!isset($included[$path])) {
            $included[$path] = true;
            include $path;
        }
    }

    /**
     * Write a message to log file
     * @param String $message message to write
     * @todo improve
     */
    public static function write_error_log($message) {
        error_log( self::$mode . ',' . self::$method . ',' . self::$controller . ':' . $message);
    }

    /**
     * Step callbacks
     */

    const STEP_AFTER_AUTHENTICATION_CHECK = 'STEP_AFTER_AUTHENTICATION_CHECK';
    const STEP_BEFORE_REQUIRE_CONTROLLER = 'STEP_BEFORE_REQUIRE_CONTROLLER';
    const STEP_BEFORE_CALL_METHOD = 'STEP_BEFORE_CALL_METHOD';
    const STEP_BEFORE_CLOSE = 'STEP_BEFORE_CLOSE';

    /**
     * Hold all step callbacks
     * @var array Array of arrays
     */
    private static $step_callback = [];

    /**
     * Add a step callback
     *
     * Step callbacks, are callbacks that executed when the API reaches
     * a certain step, multiple callbacks can be set for the same step.
     * @param string $step
     * @param function $callback
     * @since 0.1.1
     * @throws \Exception callback_is_not_function_exception
     */
    public function add_step_callback($step, $callback) {
        \Phramework\API\models\validate::enum($step, [
            self::STEP_BEFORE_REQUIRE_CONTROLLER,
            self::STEP_BEFORE_CALL_METHOD,
            self::STEP_BEFORE_CLOSE,
        ]);
        if(!is_callable($callback)) {
            throw new \Exception(__('callback_is_not_function_exception'));
        }
        //If empty
        if(!isset(self::$step_callback[$step])) {
            //Initialize array
            self::$step_callback[$step]=[];
        }

        //Push
        self::$step_callback[$step][] = $callback;
    }

    /**
     * Execute all callbacks set for this step
     * @param string $step
     */
    private static function call_callback($step) {
        if(!isset(self::$step_callback[$step])) {
            return;
        }
        foreach(self::$step_callback[$step] as $s) {
            if(!is_callable($s)) {
                throw new \Exception(__('callback_is_not_function_exception'));
            }
            $s();
        }
    }
}
