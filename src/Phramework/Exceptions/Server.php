<?php

namespace Phramework\Exceptions;

/**
 * Server Exception
 * Used to throw an \Exception, when there is a server issue.
 */
class Server extends \Exception
{
    public function __construct($message = 'Internal Server Error', $code = 500)
    {
        parent::__construct($message, $code);
    }
}
