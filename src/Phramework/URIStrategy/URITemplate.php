<?php
namespace Phramework\URIStrategy;

use Phramework\API;
use Phramework\Exceptions\Permission;
use Phramework\Exceptions\NotFound;
use \Phramework\Models\Util;

/**
 * IURIStrategy Interface
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

    public static function test($template, $uri)
    {

    }

    public function invoke($method, $params, $headers)
    {
        header('Content-Type: text/html; charset=utf-8');
        $REDIRECT_QUERY_STRING = (isset($_SERVER['REDIRECT_QUERY_STRING']) ? $_SERVER['REDIRECT_QUERY_STRING']
                    : '');
        $REDIRECT_URL          = $_SERVER['REDIRECT_URL'];

        //^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?

        $uri = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

        //$uri = '/' . trim(str_replace($uri, '', $_SERVER['REQUEST_URI']), '/');
        $uri = '/' . trim(str_replace($uri, '', $REDIRECT_URL), '/');
        $uri = urldecode($uri) . '/';

        $uri = trim($uri, '/');

        $this->p($uri);
        $this->p($REDIRECT_QUERY_STRING);
        $params = [];
        parse_str($REDIRECT_QUERY_STRING, $params);
        $this->p($params);

        foreach ($this->templates as $t) {
            // trim any slashes from begining and the end of the URI parameter
            $template = trim($t[0], '/');
            $this->p($t);
            // espace slash / character
            $template = str_replace('/', '\/', $template);
            // replace all named parameters {id} to named regexp matches
            $template = preg_replace(
                '/(.*?)\{([a-zA-Z][a-zA-Z0-9_]+)\}(.*?)/',
                '$1(?P<$2>[0-9a-zA-Z]+)$3',
                $template
            );

            $regexp = '/^' . $template . '$/';
            $this->p($regexp);
            $matches;

            if (preg_match($regexp, $uri, $matches)) {
                echo '<strong>';
                $this->p(['We have a match!', $t[1], $t[2]]);
                $this->p($matches);

                //keep non integer keys (only named matches)
                foreach ($matches as $key => $value) {
                    if (is_int($key)) {
                        unset($matches[$key]);
                    }
                }
                $this->p($matches);
                echo '</strong>';
                break;
            }
        }
    }
}
