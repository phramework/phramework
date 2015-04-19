<?php

namespace Phramework\API\exceptions;

/**
 * NotFoundException
 * Used to throw an \Exception, when the requested resource is not found.
 */
class not_found extends \Exception {

    public function __construct($message, $code = 404) {
        parent::__construct($message, $code);
    }

}
