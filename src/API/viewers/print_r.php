<?php

namespace Phramework\API\viewers;

/**
 * print_r implementation of IViewer
 * @author Xenophon Spafaridis <nohponex@gmail.com>
 * @sinse 0.1.2
 */
class print_r implements \Phramework\API\viewers\IViewer {

    /**
     * Display output
     * 
     * @param array $parameters Output parameters to display
     */
    public function view($parameters) {
        print_r($parameters);
    }

}
