<?php

namespace Phramework\Viewers;

/**
 * implementation of IViewer for jsonapi
 * @author Xenophon Spafaridis <nohponex@gmail.com>
 * @sinse 0.1.2
 */
class JSONAPI implements \Phramework\Viewers\IViewer
{
    /**
     * Display output
     *
     * @param array $parameters Output parameters to display
     */
    public function view($parameters)
    {
        if (!headers_sent()) {
            header('Content-type: application/json;charset=utf-8');
        }

        //include JSON API Object
        $parameters['jsonapi'] = ['version' => '1.0'];

        echo json_encode($parameters);
    }
}
