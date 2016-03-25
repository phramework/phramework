<?php
/**
 * Copyright 2015-2016 Xenofon Spafaridis
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
     * @param array|object $parameters Output parameters to display
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
