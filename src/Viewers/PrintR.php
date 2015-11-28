<?php

namespace Phramework\Viewers;

/**
 * print_r implementation of IViewer
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @sinse 0.1.2
 */
class PrintR implements \Phramework\Viewers\IViewer
{
    /**
     * Display output
     *
     * @param array $parameters Output parameters to display
     */
    public function view($parameters)
    {
        print_r($parameters);
    }
}
