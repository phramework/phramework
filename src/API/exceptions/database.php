<?php

namespace Phramework\API\exceptions;

/**
 * DatabaseException
 * Used to throw an exception, when there is something wrong with a database request.
 */
class database extends \Exception {

    //Error message
    private $error;

    /**
     * 
     * @global type $settings
     * @todo Notify administrators
     * @param type $message Exception message
     * @param type $error Internal error message
     */
    public function __construct($message, $error) {
        //global $settings;
        //if( $settings[ 'debug'] ) {
        //   parent::__construct( $error, 666 );
        //}else{
        parent::__construct($message, 666);
        //}
        $this->$error = $message;
        //ToDo Notify administrators!!
    }

}
