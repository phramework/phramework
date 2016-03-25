<?php

namespace Phramework\Viewers;

/**
 * json implementation of IViewer
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @since 0.1.2
 */
class JSON implements \Phramework\Viewers\IViewer
{
    /**
     * Display output
     *
     * @param array $parameters Output parameters to display
     */
    public function view($parameters)
    {
        if (!headers_sent()) {
            header('Content-Type: application/json;charset=utf-8');
        }

        //If JSONP requested (if callback is requested though GET)
        if (($callback = \Phramework\Phramework::getCallback())) {
            echo $callback;
            echo '([';
            echo json_encode($parameters);
            echo '])';
        } else {
            echo json_encode($parameters);
        }
    }
}
