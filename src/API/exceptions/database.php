<?php

namespace Phramework\API\exceptions;

/**
 * DatabaseException
 * Used to throw an exception, when there is something wrong with a database request.
 */
class database extends \Exception {

    /**
     * Database exception
     * 
     * @todo Notify administrators
     * @param string $message Exception message
     * @param string $error Internal error message
     */
    public function __construct($message, $error = NULL) {
        if (\Phramework\API\API::get_setting('debug') && $error) {
            parent::__construct($error, 666);
        } else {
            parent::__construct($message, 666);
        }
    }

}