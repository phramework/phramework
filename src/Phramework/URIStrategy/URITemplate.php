<?php
namespace Phramework\URIStrategy;

use Phramework\API;
use Phramework\Exceptions\Permission;
use Phramework\Exceptions\NotFound;
use Phramework\Exceptions\Server;
use Phramework\Models\Util;

/**
 * IURIStrategy implementation using URI templates
 * @author Xenophon Spafaridis <nohponex@gmail.com>
 * @since 1.0.0
 */
class URITemplate implements IURIStrategy
{
    private $templates;

    public function __construct($templates)
    {
        $this->templates = $templates;
    }

    private function p($param)
    {
        echo "<pre>";
        print_r($param);
        echo "</pre>";
    }

    public function test($uri_template, $uri)
    {
        $template = trim($uri_template, '/');

        // espace slash / character
        $template = str_replace('/', '\/', $template);
        // replace all named parameters {id} to named regexp matches
        $template = preg_replace(
            '/(.*?)\{([a-zA-Z][a-zA-Z0-9_]+)\}(.*?)/',
            '$1(?P<$2>[0-9a-zA-Z]+)$3',
            $template
        );

        $regexp = '/^' . $template . '$/';

        $template_parameters = [];

        if (preg_match($regexp, $uri, $template_parameters)) {
            //keep non integer keys (only named matches)
            foreach ($template_parameters as $key => $value) {
                if (is_int($key)) {
                    unset($template_parameters[$key]);
                }
            }

            return [$template_parameters];
        }

        return false;
    }

    public function uri()
    {
        $REDIRECT_QUERY_STRING = (
            isset($_SERVER['REDIRECT_QUERY_STRING']) ? $_SERVER['REDIRECT_QUERY_STRING']
            : ''
        );

        $REDIRECT_URL          = $_SERVER['REDIRECT_URL'];

        $uri = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

        $uri = '/' . trim(str_replace($uri, '', $REDIRECT_URL), '/');
        $uri = urldecode($uri) . '/';

        $uri = trim($uri, '/');

        $parameters = [];
        parse_str($REDIRECT_QUERY_STRING, $parameters);


        return [$uri, $parameters];
    }

    public function invoke($requestMethod, $requestParameters, $requestHeaders)
    {
        // Get request uri and uri parameters
        list($uri, $uri_parameters) = $this->uri();

        foreach ($this->templates as $template) {
            $uri_template = $template[0];
            //Test if uri matches the current uri template
            $test = $this->test($uri_template, $uri);

            if ($test !== false) {
                list($uri_parameters) = $test;

                $class  = $template[1];
                $method = $template[2];

                //Merge all available parameters
                $parameters = array_merge($requestParameters, $uri_parameters, $test[0]);

                /**
                 * Check if the requested controller and model is callable
                 * In order to be callable :
                 * @todo
                 */
                if (!is_callable("$class::$method")) {
                    throw new Server(API::getTranslated('method_NotFound_exception'));
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

        return false;
    }
}
