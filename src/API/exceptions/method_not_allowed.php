<?php

namespace Phramework\API\exceptions;
/**
 * method_not_allowed
 * Used to throw an \Exception, when this method is not allowed
 * to apply to this resource, or the current status of the resource.
 */
class method_not_allowed extends \Exception {
    
    //Array with the Allowed methods
    private $allowed_methods;
    
    /**
     *
     * @param array $message \Exception message
     * @param array $allowed_methods Allowed methods, should be returned in allow header.
     * @param integer $code Error code, Optional default 405
     */
    public function __construct($message, $allowed_methods = [], $code = 405) {

        parent::__construct($message, $code);
        $this->$allowed_methods = $allowed_methods;
    }
    

    public function getAllowedMethods() {
        return  $this->$allowed_methods;
    }

}
