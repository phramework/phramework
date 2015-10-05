<?php

namespace Phramework\Viewers;

/**
 * Implementation of IViewer for jsonapi
 *
 * Sends `Content-type: application/vnd.api+json` response to client
 *
 * JSONP Support is disabled
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenophon Spafaridis <nohponex@gmail.com>
 * @link http://jsonapi.org/
 * @sinse 1.0.0
 */
class JSONAPI implements \Phramework\Viewers\IViewer
{
    /**
     * Display output
     *
     * @param array $parameters Output to display as json
     */
    public function view($parameters)
    {
        if (!headers_sent()) {
            header('Content-type: application/vnd.api+json;charset=utf-8');
        }

        //include JSON API Object
        $parameters['jsonapi'] = ['version' => '1.0'];

        echo json_encode($parameters);
    }
}
