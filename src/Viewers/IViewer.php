<?php
namespace Phramework\Viewers;

/**
 * Viewer interface
 *
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @sinse 0.1.2
 */
interface IViewer
{
    /**
     * Display output
     *
     * @param array $parameters Output parameters to display
     */
    public function view($parameters);
}
