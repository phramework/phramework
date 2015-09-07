<?php

namespace Phramework\Exceptions;

/**
 * Not found exception
 *
 * The server has not found anything matching the Request-URI.
 * No indication is given of whether the condition is temporary or permanent.
 */
class NotFound extends \Exception
{
    public function __construct($message, $code = 404)
    {
        parent::__construct($message, $code);
    }
}
