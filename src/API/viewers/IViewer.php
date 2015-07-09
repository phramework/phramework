<?php
namespace Phramework\API\viewers;

/**
 * Viewer interface
 *
 * @author Xenophon Spafaridis <nohponex@gmail.com>
 * @sinse 0.1.2
 */
interface IViewer {

    /**
     * Display output
     *
     * @param array $parameters Output parameters to display
     */
    public function view($parameters);
}
