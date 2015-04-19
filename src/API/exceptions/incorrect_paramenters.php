<?php

namespace Phramework\API\exceptions;

/**
 * IncorrectParamentersException
 * Used to throw an \Exception, when there are some incorrect formed parameters.
 */
class incorrect_paramenters extends \Exception {

    //Array with the parameters
    private $parameters;

    /**
     * 
     * @param array $parameters Array with the names of incorrect parameters
     */
    public function __construct($parameters) {
        parent::__construct('incorrect_parameters_exception', 400);
        $this->parameters = $parameters;
    }

    public function getParameters() {
        return $this->parameters;
    }

}