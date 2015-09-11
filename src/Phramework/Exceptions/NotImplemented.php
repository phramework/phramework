<?php

namespace Phramework\Exceptions;

/**
 * Not Implemented exception
 *
 * The server does not support the functionality required to fulfill the request.
 * This is the appropriate response when the server does not recognize the
 * request method and is not capable of supporting it for any resource.
 * @since 1.0.0
 */
class NotImplemented extends \Exception
{
    public function __construct($message = 'Not Implemented', $code = 501)
    {
        parent::__construct($message, $code);
    }
}
