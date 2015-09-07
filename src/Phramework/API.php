<?php

namespace Phramework;

use Phramework\Models\Util;
use Phramework\Extensions\StepCallback;

// Tell PHP that we're using UTF-8 strings until the end of the script
mb_internal_encoding('UTF-8');

// Tell PHP that we'll be outputting UTF-8 to the browser
mb_http_output('UTF-8');

//TODO remove
if (!function_exists('__')) {
    function __($key)
    {
        return API::getTranslated($key);
    }
}

//TODO remove
if (!function_exists('___')) {
    function ___($key)
    {
        return API::getTranslated($key);
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
class API
{
    protected static $instance;
    /**
     * Allowed controllers
     * @var array
     */
    //private static $controller_whitelist;

    /**
     * Controllers that doesn't require authentication
     * @var array
     */
    //private static $controller_unauthenticated_whitelist;

    /**
     * Controllers that doesn't require authentication
     * @var array
     */
    //private static $controller_public_whitelist;
    private static $user;
    private static $language;
    private static $settings;
    private static $mode;

    /**
     * Viewer class
     */
    private static $viewer = 'Phramework\Viewers\json';
    /**
     * $URIStrategy object
     */
    private static $URIStrategy;

    private static $controller;
    private static $method;

    /**
     * JSONP callback, When NULL no JSONP callback is set
     * @var type
     */
    private static $callback = null;

    /**
     * Exposed extensions
     */

    /**
     * StepCallback extension
     * @var Phramework\Extensions\StepCallback
     */
    public $StepCallback;
    /**
     * translation extension
     * @var Phramework\Extensions\translation
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
     * @param object|NULL $translation_object [optional] Set custom translation class
     */
    public function __construct(
        $settings,
        $URIStrategy_object,
        $mode = self::MODE_DEFAULT,
        $translation_object = null
    ) {
        self::$settings = $settings;

        self::$mode = $mode;

        self::$user = false;
        self::$language = 'en';

        //Instantiate StepCallback object
        $this->StepCallback = new \Phramework\Extensions\StepCallback();

        if (!is_subclass_of($URIStrategy_object, 'Phramework\URIStrategy\IURIStrategy', true)) {
            throw new \Phramework\Exceptions\Server('class_is_not_implementing Phramework\URIStrategy\IURIStrategy');
        }
        self::$URIStrategy = $URIStrategy_object;

        //If custom translation object is set add it
        if ($translation_object) {
            $this->setTranslationObject($translation_object);
        } else {
            //Or instantiate default translation object
            $this->translation = new \Phramework\Extensions\Translation(
                self::getSetting('language'),
                self::getSetting('translation', 'track_missing_keys')
            );
        }

        self::$instance = $this;
    }

    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     * Authentication class (Full namespace)
     */
    private static $authenticationClass = 'Phramework\Models\Authentication';

    /**
     * Set authentication class
     * @param string $class A name of class that extends \Phramework\Models\authentication
     */
    public static function setAuthenticationClass($class)
    {
        if (!is_subclass_of($class, 'Phramework\Models\Authentication', true)) {
            throw new \Exception('class_is_not_implementing Phramework\Models\Authentication');
        }
        self::$authenticationClass = $class;
    }

    /**
     * Authenticate a user
     *
     * The result will be stored at internal $user variable
     * @param type $username
     * @param type $password
     * @param array|FALSE Returns the user object
     */
    public static function authenticate($username, $password)
    {
        return call_user_func([self::$authenticationClass, 'authenticate'], $username, $password);
    }

    public function setTranslationObject($translation_object)
    {
        if (!is_subclass_of($class, 'Phramework\Extensions\translation', true)) {
            throw new \Exception('class_is_not_implementing Phramework\Extensions\translation');
        }
        $this->translation = $translation_object;
    }

    /**
     * Shortcut function alias of $this->translation->getTranslated
     * @param type $key
     * @param type $parameters
     * @param type $fallback_value
     * @return type
     */
    public static function getTranslated($key, $parameters = null, $fallback_value = null)
    {
        return self::$instance->translation->getTranslated($key, $parameters, $fallback_value);
    }

    //Allowed methods
    public static $method_whitelist = [
        self::METHOD_GET,
        self::METHOD_POST,
        self::METHOD_DELETE,
        self::METHOD_PUT,
        self::METHOD_HEAD,
        self::METHOD_OPTIONS,
        self::METHOD_PATCH
    ];

    /**
     * Execute the API
     * @throws exceptions\permission
     * @throws exceptions\NotFound
     * @todo change default timezone
     * @todo change default language
     */
    public function invoke()
    {
        try {
            date_default_timezone_set('Europe/Athens');

            if (self::getSetting('debug')) {
                error_reporting(E_ALL);
                ini_set('display_errors', '1');
            }

            if (self::getSetting('errorlog_path')) {
                ini_set('error_log', self::getSetting('errorlog_path'));
            }

            //Check if callback is set (JSONP)
            if (isset($_GET['callback'])) {
                if (!API\models\Validate::isValidCallback($_GET['callback'])) {
                    throw new exceptions\IncorrectParameters(['callback']);
                }
                self::$callback = $_GET['callback'];
                unset($_GET['callback']);
            }

            //Initialize metaphrase\phpsdk\Metaphrase
            //$metaphrase = new \metaphrase\phpsdk\Metaphrase($settings['translate']['api_key']);
            //Initialize Database connection if required or db set
            if (self::getSetting('require_db') || self::getSetting('db')) {
                Models\Database::requireDatabase(self::getSetting('db'));
            }

            //Unset from memory Database connection information
            unset(self::$settings['db']);

            //Get method from the request (HTTP) method
            self::$method = $method =
                isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : self::METHOD_GET;

            //Default value of response's header origin
            $origin = '*';

            //Get request headers
            $headers = Util::headers();

            //Check origin header
            if (isset($headers['Origin'])) {
                $origin_host = parse_url($headers['Origin'], PHP_URL_HOST);
                //Check if origin host is allowed
                if ($origin_host && in_array($origin_host, self::getSetting('allowed_referer'))) {
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
            if ($method == self::METHOD_OPTIONS) {
                header('HTTP/1.1 200 OK');
                exit();
            }

            //Authenticate request (check authentication)
            self::$user = call_user_func(array(self::$authenticationClass, 'check'));

            //STEP_AFTER_AUTHENTICATION_CHECK
            $this->StepCallback->call(StepCallback::STEP_AFTER_AUTHENTICATION_CHECK);

            //Default language value
            $language = self::getSetting('language');

            //Select request's language
            if (isset($_GET['this_language']) && self::getSetting('languages') &&
                in_array($_GET['this_language'], self::getSetting('languages'))
            ) { //Force requested language
                if ($_GET['this_language'] != $language) {
                    $language = $_GET['this_language'];
                }
                unset($_GET['this_language']);
            } elseif (self::$user && isset(self::$user['language_code'])) { // Use user's langugae
                $language = self::$user['language_code'];
            } elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && self::getSetting('languages')) { // Use Accept languge if provided
                $a = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

                if (in_array($a, self::getSetting('languages'))) {
                    $language = $a;
                }
            }

            //Set language variable
            self::$language = $language;
            $this->translation->setLanguageCode($language);



            //STEP_BEFORE_REQUIRE_CONTROLLER
            $this->StepCallback->call(StepCallback::STEP_BEFORE_REQUIRE_CONTROLLER);

            //Override method HEAD.
            // When HEAD method is called the GET method will be executed but no response boy will be send
            // we update the value of local variable $method sinse then original
            // requested method is stored at API::$method
            if ($method == self::METHOD_HEAD) {
                $method = self::METHOD_GET;
            }

            //is callable

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
            $this->StepCallback->call(StepCallback::STEP_BEFORE_CALL_METHOD);

            //Call controller's method
            self::$URIStrategy->invoke($method, $params, $headers);

            //Unset all
            unset($params);

            //STEP_BEFORE_CLOSE
            $this->StepCallback->call(StepCallback::STEP_BEFORE_CLOSE);


        } catch (exceptions\NotFound $exception) {
            self::writeErrorLog(
                $exception->getMessage() .
                (isset($_SERVER['HTTP_REFERER']) ? ' from ' .
                    Util::userContent($_SERVER['HTTP_REFERER']) : '')
            );
            self::errorView([
                'code' => $exception->getCode(),
                'error' => $exception->getMessage()
            ]);
        } catch (exceptions\Request $exception) {
            self::errorView([
                'code' => $exception->getCode(),
                'error' => $exception->getMessage()]);
        } catch (exceptions\Permission $exception) {
            self::writeErrorLog(
                $exception->getMessage()
            );
            self::errorView(['code' => 403,
                'error' => $exception->getMessage(),
                'title' => 'Permission'
            ]);
        } catch (exceptions\MissingParamenters $exception) {
            self::writeErrorLog(
                $exception->getMessage() .
                implode(', ', $exception->getParameters())
            );

            if (self::getSetting('debug')) {
                self::errorView([
                    'code' => $exception->getCode(),
                    'error' => $exception->getMessage(),
                    'missing' => $exception->getParameters(),
                    'title' => 'MissingParamenters'
                ]);
            } else {
                self::errorView([
                    'code' => $exception->getCode(),
                    'error' => $exception->getMessage(),
                    'missing' => $exception->getParameters(),
                    'title' => 'MissingParamenters'
                ]);
            }
        } catch (exceptions\IncorrectParameters $exception) {
            self::writeErrorLog(
                $exception->getMessage() . implode(', ', array_keys($exception->getParameters()))
            );
            self::errorView([
                'code' => 400,
                'error' => $exception->getMessage() . ' : ' . implode(', ', array_keys($exception->getParameters())),
                'incorrect' => $exception->getParameters(),
                'title' => 'incorrect_parameters_exception'
            ]);
        } catch (exceptions\method_not_allowed $exception) {
            self::writeErrorLog(
                $exception->getMessage()
            );

            //write allow header if AllowedMethods is set
            if (!headers_sent() && $exception->getAllowedMethods()) {
                header('Allow: ' . implode(', ', $exception->getAllowedMethods()));
            }

            self::errorView([
                'code' => 400,
                'error' => $exception->getMessage() . ' : ' . implode(', ', array_keys($exception->getParameters())),
                'allow' => $exception->getAllowedMethods(),
                'title' => 'method_not_allowed'
            ]);
        } catch (\Exception $exception) {
            self::writeErrorLog(
                $exception->getMessage()
            );
            self::errorView([
                'code' => 400,
                'error' => $exception->getMessage(),
                'title' => 'error'
            ]);
        } finally {
            //Try to close the databse
            Models\Database::close();
        }
    }

    /**
     * Get current user
     * @return array|FALSE Get current user's object
     */
    public static function getUser()
    {
        return self::$user;
    }

    /**
     * Get timezone offset in minutes
     *
     * This value is based on user's timezone
     * @todo Implement, get user's data
     * @return integer Returns offset from UTC in minutes
     */
    public static function getTimezoneOffset()
    {
        return + 2 * 60;
    }

    /**
     * Get current language
     * @return string Current language
     */
    public static function getLanguage()
    {
        return self::$language;
    }

    /**
     * Get requested method
     * @return string
     */
    public static function getMethod()
    {
        return self::$method;
    }

    /**
     * Get requested controller
     * @return string
     */
    public static function getController()
    {
        return self::$controller;
    }

    /**
     * Get requested mode
     * @return string
     */
    public static function getMode()
    {
        return self::$mode;
    }

    /**
     * Get current viewer
     * @return string
     */
    public static function getViewer()
    {
        return self::$viewer;
    }

    /**
     * Get callback if set
     * @return string
     */
    public static function getCallback()
    {
        return self::$callback;
    }

    /**
     * Get a setting value
     * @param string $key The requested setting key
     * @param string|NULL $second_level
     * @param mixed $default_value [optional] Default value is the setting is missing.
     * @return Mixed Returns the value of setting, NULL when not found
     */
    public static function getSetting($key, $second_level = null, $default_value = null)
    {
        if (!isset(self::$settings[$key]) || ($second_level && isset(self::$settings[$key][$second_level]))) {
            return $default_value;
        }
        if ($second_level) {
            self::$settings[$key][$second_level];
        }

        return self::$settings[$key];
    }

    /**
     * Set viewer class
     * @param string $class A name of class that implements \Phramework\Viewers\IViewer
     */
    public static function setViewerClass($class)
    {
        if (!is_subclass_of($class, '\Phramework\Viewers\IViewer', true)) {
            throw new \Exception('class_is_not_implementing Phramework\Viewers\IViewer');
        }
        self::$viewer = $class;
    }

    /**
     * Output an error
     * @param array $params The error parameters. The 'error' index holds the message,
     * and the 'code' message the error code,
     * note that if headers are not send the response code will set with the 'code' value.
     */
    private static function errorView($params)
    {
        if (!headers_sent()) {
            http_response_code(
                (isset($params['code']) ? $params['code'] : 400)
            );
        }

        self::view($params);
    }

    /**
     * Output the response using the selected viewer
     *
     * If requested method is HEAD then the response body will be empty
     * Multiple arguments can be set, first argument will always be used as the parameters array.
     * Custom IViewer implementation can use these additional parameters at they definition.
     * @param array $params The output parameters. Notice $params['user'] will be overwritten if set.
     * @param integer $status The response status
     * @return null Returns nothing
     */
    public static function view($parameters = [])
    {
        $args = func_get_args();

        /**
         * On HEAD method dont return response body, only the user's object
         */
        if (self::getMethod() == self::METHOD_HEAD) {
            return;
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
    public static function writeErrorLog($message)
    {
        error_log(self::$mode . ',' . self::$method . ',' . self::$controller . ':' . $message);
    }
    const METHOD_ANY     = false;
    const METHOD_GET     = 'GET';
    const METHOD_POST    = 'POST';
    const METHOD_PUT     = 'PUT';
    const METHOD_DELETE  = 'DELETE';
    const METHOD_HEAD    = 'HEAD';
    const METHOD_PATCH   = 'PATCH';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_TRACE   = 'TRACE';
}
