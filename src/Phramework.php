<?php
/**
 * Copyright 2015 Spafaridis Xenofon
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
namespace Phramework;

use \Phramework\Models\Util;
use \Phramework\Models\Request;
use \Phramework\Extensions\StepCallback;

// @codingStandardsIgnoreStart
// Tell PHP that we're using UTF-8 strings until the end of the script
mb_internal_encoding('UTF-8');

// Tell PHP that we'll be outputting UTF-8 to the browser
mb_http_output('UTF-8');
// @codingStandardsIgnoreEnd

/**
 * API 'framework' by NohponeX
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenofon <nohponex@gmail.com>
 * @version 1.0.0
 * @link https://nohponex.gr Developer's website
 * @todo Use parameters
 * @todo Create a class for settings
 * @todo Clean GET callback
 * @todo Add localization class
 */
class Phramework
{
    protected static $instance;

    private static $user;
    private static $language;
    private static $settings;
    private static $mode;

    /**
     * Viewer class
     */
    private static $viewer = \Phramework\Viewers\JSON::class;//'\Phramework\Viewers\JSON';
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
     * @param object|NULL $translationObject [optional] Set custom translation class
     */
    public function __construct(
        $settings,
        $URIStrategyObject,
        $mode = self::MODE_DEFAULT,
        $translationObject = null
    ) {
        self::$settings = $settings;

        self::$mode = $mode;

        self::$user = false;
        self::$language = 'en';

        //Instantiate StepCallback object
        $this->StepCallback = new \Phramework\Extensions\StepCallback();

        if (!is_subclass_of($URIStrategyObject, '\Phramework\URIStrategy\IURIStrategy', true)) {
            throw new \Phramework\Exceptions\ServerException(
                'Class is not implementing Phramework\URIStrategy\IURIStrategy'
            );
        }
        self::$URIStrategy = $URIStrategyObject;

        //If custom translation object is set add it
        if ($translationObject) {
            $this->setTranslationObject($translationObject);
        } else {
            //Or instantiate default translation object
            $this->translation = new \Phramework\Extensions\Translation(
                self::getSetting('language'),
                self::getSetting('translation', 'track_missing_keys', null, false)
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
    private static $authenticationClass = '\Phramework\Models\Authentication';

    /**
     * Set authentication class
     * @param string $class A name of class that extends \Phramework\Models\authentication
     */
    public static function setAuthenticationClass($class)
    {
        if (!is_subclass_of($class, '\Phramework\Models\Authentication', true)) {
            throw new \Exception(
                'Class is not implementing Phramework\Models\Authentication'
            );
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

    public function setTranslationObject($translationObject)
    {
        if (!is_subclass_of($translationObject, '\Phramework\Extensions\translation', true)) {
            throw new \Exception(
                'Class is not implementing Phramework\Extensions\translation'
            );
        }
        $this->translation = $translationObject;
    }

    /**
     * Shortcut function alias of $this->translation->getTranslated
     * @param type $key
     * @param type $parameters
     * @param type $fallbackValue
     * @return type
     */
    public static function getTranslated($key, $parameters = null, $fallbackValue = null)
    {
        return self::$instance->translation->getTranslated($key, $parameters, $fallbackValue);
    }

    /**
     * Allowed HTTP methods
     * @var string[]
     */
    public static $methodWhitelist = [
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
     * @throws \Phramework\Exceptions\PermissionException
     * @throws \Phramework\Exceptions\NotFoundException
     * @todo change default timezone
     * @todo change default language
     * TODO @security deny access to any else referalls
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
                if (!\Phramework\Validate\Validate::isValidJsonpCallback($_GET['callback'])) {
                    throw new \Phramework\Exceptions\IncorrectParametersException(
                        ['callback']
                    );
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
            $method = self::$method =
                isset($_SERVER['REQUEST_METHOD'])
                ? $_SERVER['REQUEST_METHOD']
                : self::METHOD_GET;

            //Check if the requested HTTP method method is allowed
            // @todo check error code
            if (!in_array($method, self::$methodWhitelist)) {
                throw new \Phramework\Exceptions\RequestExceptionException(
                    'Method not allowed'
                );
            }

            //Default value of response's header origin
            $origin = '*';

            //Get request headers
            $headers = Models\Request::headers();

            //Check origin header
            if (isset($headers['Origin'])) {
                $origin_host = parse_url($headers['Origin'], PHP_URL_HOST);
                //Check if origin host is allowed
                if ($origin_host && self::getSetting('allowed_referer')
                    && in_array($origin_host, self::getSetting('allowed_referer'))) {
                    $origin = $headers['Origin'];
                }
                //@TODO @security else deny access
            } elseif (isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI'])) {
                $origin = '*'; //TODO Exctract origin from request url
            }

            //Send access control headers
            if (!headers_sent()) {
                header('Access-Control-Allow-Credentials: true');
                header('Access-Control-Allow-Origin: ' . $origin);
                header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, HEAD, DELETE, OPTIONS');
                header(
                    'Access-Control-Allow-Headers: '
                    . 'Origin, X-Requested-With, Content-Type, Accept, Authorization, Content-Encoding'
                );
            }

            //Catch OPTIONS request and kill it
            if ($method == self::METHOD_OPTIONS) {
                header('HTTP/1.1 200 OK');
                exit();
            }

            //Authenticate request (check authentication)
            self::$user = call_user_func(
                [self::$authenticationClass, 'check']
            );

            //In case of array returned force type to be object
            if (is_array(self::$user)) {
                self::$user = (object)self::$user;
            }

            //STEP_AFTER_AUTHENTICATION_CHECK
            $this->StepCallback->call(
                StepCallback::STEP_AFTER_AUTHENTICATION_CHECK
            );

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
            } elseif (self::$user && property_exists(self::$user, 'language_code')) {
                // Use user's langugae
                $language = self::$user->language_code;
            } elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && self::getSetting('languages')) {
                // Use Accept languge if provided
                $a = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

                if (in_array($a, self::getSetting('languages'))) {
                    $language = $a;
                }
            }

            //Set language variable
            self::$language = $language;
            $this->translation->setLanguageCode($language);

            //STEP_BEFORE_REQUIRE_CONTROLLER
            $this->StepCallback->call(
                StepCallback::STEP_BEFORE_REQUIRE_CONTROLLER
            );

            //Override method HEAD.
            // When HEAD method is called the GET method will be executed but no response boy will be send
            // we update the value of local variable $method sinse then original
            // requested method is stored at Phramework::$method
            //if ($method == self::METHOD_HEAD) {
            //    $method = self::METHOD_GET;
            //}

            //Merge all REQUEST parameters into $params array
            $params = array_merge($_GET, $_POST, $_FILES); //TODO $_FILES only if POST OR PUT

            //unset($_GET, $_POST, $_FILES);

            //Parse request body
            //@Todo needs additional attencion
            //@Todo add allowed content-types
            if (in_array(
                $method,
                [
                    self::METHOD_POST,
                    self::METHOD_PATCH,
                    self::METHOD_PUT,
                    self::METHOD_DELETE
                ]
            )) {
                if (isset($headers[Request::HEADER_CONTENT_TYPE])
                    && $headers[Request::HEADER_CONTENT_TYPE]
                        == 'application/x-www-form-urlencoded'
                ) {
                    //Decode and merge params
                    parse_str(file_get_contents('php://input'), $input);

                    if ($input && !empty($input)) {
                        $params = array_merge($params, $input);
                    }
                } elseif (isset($headers[Request::HEADER_CONTENT_TYPE])
                    && in_array(
                        $headers[Request::HEADER_CONTENT_TYPE],
                        ['application/json', 'application/vnd.api+json']
                    )
                ) {
                    //@TODO add regexp for json

                    $input = trim(file_get_contents('php://input'));

                    //note if input length is >0 and decode returns null then its bad data
                    //json_last_error()

                    $input = json_decode($input, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Phramework\Exceptions\RequestException(
                            'JSON parse error - ' . json_last_error_msg()
                        );
                    }

                    if ($input && !empty($input)) {
                        $params = array_merge($params, $input);
                    }
                }
            }

            //STEP_BEFORE_CALL_METHOD
            $this->StepCallback->call(
                StepCallback::STEP_BEFORE_CALL_METHOD
            );

            //Call controller's method
            self::$URIStrategy->invoke($method, $params, $headers, self::$user);

            //Unset all
            unset($params);

            //STEP_BEFORE_CLOSE
            $this->StepCallback->call(StepCallback::STEP_BEFORE_CLOSE);


        } catch (\Phramework\Exceptions\NotFoundException $exception) {
            self::writeErrorLog(
                $exception->getMessage()
            );

            self::errorView([[
                'status' => $exception->getCode(),
                'detail' => $exception->getMessage(),
                'title' => $exception->getMessage()
            ]], $exception->getCode());
        } catch (\Phramework\Exceptions\RequestExceptionException $exception) {
            self::errorView([[
                'status' => $exception->getCode(),
                'detail' => $exception->getMessage(),
                'title' => $exception->getMessage()
            ]], $exception->getCode());
        } catch (\Phramework\Exceptions\PermissionException $exception) {
            self::writeErrorLog(
                $exception->getMessage()
            );

            self::errorView([[
                'status' => $exception->getCode(),
                'detail' => $exception->getMessage(),
                'title' => $exception->getMessage()
            ]], $exception->getCode());
        } catch (\Phramework\Exceptions\MissingParametersException $exception) {
            self::writeErrorLog(
                $exception->getMessage()
            );

            if (self::getSetting('debug')) {
                //todo
            }

            self::errorView([[
                'status' => $exception->getCode(),
                'detail' => $exception->getMessage(),
                'meta' => [
                    'missing' => $exception->getParameters()
                ],
                'title' => $exception->getMessage()
            ]], $exception->getCode());
        } catch (\Phramework\Exceptions\IncorrectParametersException $exception) {
            self::writeErrorLog(
                $exception->getMessage()
            );

            self::errorView([[
                'status' => $exception->getCode(),
                'detail' => $exception->getMessage(),
                'meta' => [
                    'incorrect' => $exception->getParameters()
                ],
                'title' => $exception->getMessage()
            ]], $exception->getCode());
        } catch (\Phramework\Exceptions\MethodNotAllowedException $exception) {
            self::writeErrorLog(
                $exception->getMessage()
            );

            //write allow header if AllowedMethods is set
            if (!headers_sent() && $exception->getAllowedMethods()) {
                header('Allow: ' . implode(', ', $exception->getAllowedMethods()));
            }

            self::errorView([[
                'status' => $exception->getCode(),
                'detail' => $exception->getMessage(),
                'meta' => [
                    'allow' => $exception->getAllowedMethods()
                ],
                'title' => $exception->getMessage()
            ]], $exception->getCode());
        } catch (\Phramework\Exceptions\RequestException $exception) {
            self::writeErrorLog(
                $exception->getMessage()
            );

            self::errorView([[
                'status' => $exception->getCode(),
                'detail' => $exception->getMessage(),
                'title' => 'Request Εrror'
            ]], $exception->getCode());
        } catch (\Exception $exception) {
            self::writeErrorLog(
                $exception->getMessage()
            );

            self::errorView([[
                'status' => 400,
                'detail' => $exception->getMessage(),
                'title' => 'Εrror'
            ]]);
        } finally {
            //Try to close the databse
            Models\Database::close();
        }
    }

    /**
     * Get current user
     * @return \stdClass|false Get current user's object,
     * false in case of non authenticated request
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
     * @return int Returns offset from UTC in minutes
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
        if (!isset(self::$settings[$key])
            || ($second_level && isset(self::$settings[$key][$second_level]))
        ) {
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
            throw new \Exception(
                'Class is not implementing \Phramework\Viewers\IViewer'
            );
        }
        self::$viewer = $class;
    }

    /**
     * Output an error
     * @param array $params The error parameters. The 'error' index holds the message,
     * and the 'code' message the error code,
     * note that if headers are not send the response code will set with the 'code' value.
     */
    private static function errorView($errors, $code = 400)
    {
        if (!headers_sent()) {
            http_response_code($code);
        }

        self::view([
            'errors' => $errors
        ]);
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
     * @param string $message message to write
     * @todo improve
     */
    public static function writeErrorLog($message)
    {
        error_log(self::$mode . ',' . self::$method . ',' . self::$controller . ':' . $message);
    }

    const METHOD_ANY     = null;
    const METHOD_GET     = 'GET';
    const METHOD_POST    = 'POST';
    const METHOD_PUT     = 'PUT';
    const METHOD_DELETE  = 'DELETE';
    const METHOD_HEAD    = 'HEAD';
    const METHOD_PATCH   = 'PATCH';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_TRACE   = 'TRACE';
}
