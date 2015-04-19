<?php

namespace Phramework\API\exceptions;

/**
 * MissingParamentersException
 * Used to throw an \Exception, when there are some missing parameters.
 */
class missing_paramenters extends \Exception {

    //Array with the parameters
    private $parameters;

    /**
     * 
     * @param array $parameters Array with the names of missing parameters
     */
    public function __construct($parameters) {
        parent::__construct('missing_parameters_exception', 400);
        $this->parameters = $parameters;
    }

    public function getParameters() {
        return $this->parameters;
    }

}