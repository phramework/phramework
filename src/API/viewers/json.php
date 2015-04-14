<?php

namespace Phramework\API\viewers;

/**
 * json implementation of IViewer
 * @author Xenophon Spafaridis <nohponex@gmail.com>
 * @sinse 0.1.2
 */
class json implements \Phramework\API\viewers\IViewer {

    /**
     * Display output
     * 
     * @param array $parameters Output parameters to display
     */
    public function view($parameters) {
        if (!headers_sent()) {
            header('Content-type: application/json;charset=utf-8');
        }
        //If JSONP requested (if callback is requested though GET)
        if (($callback = \Phramework\API\API::get_callback())) {
            echo $callback;
            echo '([';
            echo json_encode($parameters);
            echo '])';
        } else {
            echo json_encode($parameters);
        }
    }

}
