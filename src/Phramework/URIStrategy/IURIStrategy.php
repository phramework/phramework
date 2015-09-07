<?php
namespace Phramework\URIStrategy;

/**
 * IURIStrategy Interface
 * @author Xenophon Spafaridis <nohponex@gmail.com>
 * @since 1.0.0
 */
interface IURIStrategy
{
    public function invoke($method, $params, $headers);
}
