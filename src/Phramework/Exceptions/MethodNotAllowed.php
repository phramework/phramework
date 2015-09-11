<?php

namespace Phramework\Exceptions;

/**
 * MethodNotAllowed
 * Used to throw an \Exception, when this method is not allowed
 * to apply to this resource, or the current status of the resource.
 * @author Spafaridis Xenophon <nohponex@gmail
 */
class MethodNotAllowed extends \Exception
{
    //Array with the Allowed methods
    private $allowedMethods;
    
    /**
     *
     * @param array $message \Exception message
     * @param array $allowedMethods Allowed methods, should be returned in allow header.
     * @param integer $code Error code, Optional default 405
     */
    public function __construct($message, $allowedMethods = [], $code = 405)
    {
        parent::__construct($message, $code);
        $this->$allowedMethods = $allowedMethods;
    }
    

    public function getAllowedMethods()
    {
        return  $this->$allowedMethods;
    }
}
