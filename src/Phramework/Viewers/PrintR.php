<?php

namespace Phramework\Viewers;

/**
 * print_r implementation of IViewer
 * @author Xenophon Spafaridis <nohponex@gmail.com>
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
