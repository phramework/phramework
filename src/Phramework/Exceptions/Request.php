<?php

namespace Phramework\Exceptions;

/**
 * RequestException
 * Used to throw an \Exception, when there is something wrong with the request.
 */
class Request extends \Exception
{
    /**
     *
     * @param array $message \Exception message
     * @param integer $code Error code, Optional default 400
     */
    public function __construct($message, $code = 400)
    {
        //Known error codes
        $errors = [
        ];
        if (isset($errors[$code])) {
            $message = $errors[$code];
        }
        parent::__construct($message, $code);
    }
}
