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
namespace Phramework\URIStrategy;

use \Phramework\Phramework;
use \Phramework\Exceptions\PermissionException;
use \Phramework\Exceptions\NotFoundException;
use \Phramework\Exceptions\Server;
use \Phramework\Models\Util;

/**
 * IURIStrategy implementation using URI templates
 *
 * This strategy uses URI templates to validate the requested URI,
 * if the URI matches a template then the assigned method will be executed.
 *
 * This class is the preferable strategy if jsonapi is to be used.
 *
 * It requires apache configuration via .htaccess
 * ```
 * RewriteEngine On
 *
 * #Required for URITemplate strategy
 * RewriteCond %{REQUEST_FILENAME} !-f
 * RewriteCond %{REQUEST_FILENAME} !-d
 * RewriteRule ^(.*)$ index.php [QSA,L]
 * ```
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenophon Spafaridis <nohponex@gmail.com>
 * @since 1.0.0
 */
class URITemplate implements \Phramework\URIStrategy\IURIStrategy
{
    /**
     * templates
     * @var array[]
     */
    private $templates;

    /**
     * Create a URI Template strategy
     * @param array[] $templates List of URI template and meta - information objects
     * @todo specify templates format
     */
    public function __construct($templates)
    {
        $this->templates = $templates;
    }

    /**
     * Include additional templates
     * @param array[] $templates List of URI template and meta - information objects
     * @todo specify templates format
     */
    public function addTemplates($templates)
    {
        $this->templates = array_merge($this->templates, $templates);
    }

    /**
     * Test an URI template validates the provided URI
     * @param string $URITemplate URI Template
     * @param string $URI Provided URI
     * @return false|array If the validation of the template is not successful
     * then false will be returned,
     * else a array with a key-value array in position 0 will be returned
     * containing the extracter parameters from the URI template.
     * @todo provide options to specify parameters data type (alphanumeric or int)
     * @todo provide options to define optional parameters
     */
    public function test($URITemplate, $URI)
    {
        $template = trim($URITemplate, '/');

        // espace slash / character
        $template = str_replace('/', '\/', $template);
        // replace all named parameters {id} to named regexp matches
        $template = preg_replace(
            '/(.*?)\{([a-zA-Z][a-zA-Z0-9_]+)\}(.*?)/',
            '$1(?P<$2>[0-9a-zA-Z_]+)$3',
            $template
        );

        $regexp = '/^' . $template . '$/';

        $templateParameters = [];

        if (preg_match($regexp, $URI, $templateParameters)) {
            //keep non integer keys (only named matches)
            foreach ($templateParameters as $key => $value) {
                if (is_int($key)) {
                    unset($templateParameters[$key]);
                }
            }

            return [$templateParameters];
        }

        return false;
    }

    /**
     * Get current URI and GET parameters from the requested URI
     * @return string[2] Returns an array with current URI and GET parameters
     */
    public function URI()
    {
        $REDIRECT_QUERY_STRING = (
            isset($_SERVER['REDIRECT_QUERY_STRING'])
            ? $_SERVER['REDIRECT_QUERY_STRING']
            : (
                isset($_SERVER['QUERY_STRING'])
                ? $_SERVER['QUERY_STRING']
                : ''
            )
        );

        $REDIRECT_URL = (
            isset($_SERVER['REDIRECT_URL'])
            ? $_SERVER['REDIRECT_URL']
            : (
                isset($_SERVER['REQUEST_URI'])
                ? $_SERVER['REQUEST_URI']
                : ''
            )
        );

        $URI = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

        $URI = '/' . trim(str_replace($URI, '', $REDIRECT_URL), '/');
        $URI = urldecode($URI) . '/';

        $URI = trim($URI, '/');

        $parameters = [];

        //Extract parametrs from QUERY string
        parse_str($REDIRECT_QUERY_STRING, $parameters);

        return [$URI, $parameters];
    }

    /**
     * [invoke description]
     * @param  [type] $requestMethod     [description]
     * @param  [type] $requestParameters [description]
     * @param  [type] $requestHeaders    [description]
     * @param  [type] $requestUser       [description]
     * @throws NotFoundException
     * @throws NotFoundException
     * @throws PermissionException
     * @return [type]                    [description]
     */
    public function invoke($requestMethod, $requestParameters, $requestHeaders, $requestUser)
    {
        // Get request uri and uri parameters
        list($URI, $URI_parameters) = $this->URI();

        foreach ($this->templates as $template) {
            $templateMethod = (isset($template[3]) ? $template[3] : Phramework::METHOD_ANY);
            $requiresAuthentication = (isset($template[4]) ? $template[4] : false);

            // Ignore if not a valid method
            if ((is_array($templateMethod) && !in_array($requestMethod, $templateMethod))
                    || (!is_array($templateMethod) && $templateMethod != Phramework::METHOD_ANY && $templateMethod !== $requestMethod)
            ) {
                continue;
            }

            $URITemplate = $template[0];

            //Test if uri matches the current uri template
            $test = $this->test($URITemplate, $URI);

            if ($test !== false) {
                if ($requiresAuthentication && $requestUser === false) {
                    throw new PermissionException(
                        Phramework::getTranslated('Unauthenticated Access')
                    );
                }

                list($URI_parameters) = $test;

                $class   = $template[1];
                $method  = $template[2];

                //Merge all available parameters
                $parameters = array_merge($requestParameters, $URI_parameters, $test[0]);

                /**
                 * Check if the requested controller and model is callable
                 * In order to be callable :
                 * @todo complete documentation
                 */
                if (!is_callable("$class::$method")) {
                    throw new NotFoundException(Phramework::getTranslated('Method not found'));
                }

                //Call method
                call_user_func(
                    [$class, $method],
                    $parameters,
                    $requestMethod,
                    $requestHeaders
                );
                return true;
            }
        }

        throw new NotFoundException(Phramework::getTranslated('Method not found'));
    }
}
