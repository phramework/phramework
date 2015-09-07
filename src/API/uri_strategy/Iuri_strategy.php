<?php
namespace Phramework\API\uri_strategy;
/**
 * Iuri_strategy Interface
 * @author Xenophon Spafaridis <nohponex@gmail.com>
 * @since 1.0.0
 */
interface Iuri_strategy {
    function invoke($method, $params, $headers);
}