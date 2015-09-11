<?php
namespace Phramework\URIStrategy;

use Phramework\API;
use Phramework\Exceptions\Permission;
use Phramework\Exceptions\NotFound;
use Phramework\Exceptions\Server;
use Phramework\Models\Util;

/**
 * IURIStrategy implementation using URI templates
 * 
 * 
 * This strategy uses URI templates to validate the requested URI,
 * if the URI matches a template then the assigned method will be executed.
 * This class is the preferable strategy if jsonapi is to be used.
 * @author Xenophon Spafaridis <nohponex@gmail.com>
 * @since 1.0.0
 */
class URITemplate implements Phramework\URIStrategy\IURIStrategy
{
    private $templates;
    
    /**
     * Create a URI Template strategy
     * @param Array $templates List of URI template and metainformation objects
     */
    public function __construct($templates)
    {
        $this->templates = $templates;
    }
    
    /**
     * Test an URI template validates the provided URI
     * @param string $URITemplate URI Template
     * @param string $URI Provided URI 
     * @return false|array If the validation of the template is not successful
     * then false will be returned, else a key value array will be retrned
     * containing the extracter parameters from the URI template.
     */
    public function test($URITemplate, $URI)
    {
        $template = trim($URITemplate, '/');

        // espace slash / character
        $template = str_replace('/', '\/', $template);
        // replace all named parameters {id} to named regexp matches
        $template = preg_replace(
            '/(.*?)\{([a-zA-Z][a-zA-Z0-9_]+)\}(.*?)/',
            '$1(?P<$2>[0-9a-zA-Z]+)$3',
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
            isset($_SERVER['REDIRECT_QUERY_STRING']) ? $_SERVER['REDIRECT_QUERY_STRING']
            : ''
        );

        $REDIRECT_URL = $_SERVER['REDIRECT_URL'];

        $URI = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

        $URI = '/' . trim(str_replace($URI, '', $REDIRECT_URL), '/');
        $URI = urldecode($URI) . '/';

        $URI = trim($URI, '/');

        $parameters = [];
        parse_str($REDIRECT_QUERY_STRING, $parameters);


        return [$URI, $parameters];
    }

    public function invoke($requestMethod, $requestParameters, $requestHeaders, $requestUser)
    {
        // Get request uri and uri parameters
        list($URI, $URI_parameters) = $this->URI();

        foreach ($this->templates as $template) {
            $templateMethod = (isset($template[3]) ? $template[3] : API::METHOD_ANY);
            $requiresAuthentication = (isset($template[4]) ? $template[4] : false);

            // Ignore if not a valid method
            if ($templateMethod != API::METHOD_ANY && $templateMethod != $requestMethod) {
                continue;
            }

            $URITemplate = $template[0];

            //Test if uri matches the current uri template
            $test = $this->test($URITemplate, $URI);

            if ($test !== false) {
                if ($requiresAuthentication && $requestUser === false) {
                    throw new Permission(API::getTranslated('unauthenticated_access_exception'));
                }

                list($URI_parameters) = $test;

                $class   = $template[1];
                $method  = $template[2];

                //Merge all available parameters
                $parameters = array_merge($requestParameters, $URI_parameters, $test[0]);

                /**
                 * Check if the requested controller and model is callable
                 * In order to be callable :
                 * @todo
                 */
                if (!is_callable("$class::$method")) {
                    throw new NotFound(API::getTranslated('method_NotFound_exception'));
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

        throw new NotFound(API::getTranslated('method_NotFound_exception'));
    }
}
