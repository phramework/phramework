<?php

namespace Phramework\Exceptions;

/**
 * DatabaseException
 * Used to throw an \Exception, when there is something wrong with a Database request.
 */
class Database extends \Exception
{
    /**
     * Database \Exception
     *
     * @todo Notify administrators
     * @param string $message \Exception message
     * @param string $error Internal error message
     */
    public function __construct($message, $error = null)
    {
        if (\Phramework\API::getSetting('debug') && $error) {
            parent::__construct($error, 666);
        } else {
            parent::__construct($message, 666);
        }
    }
}
