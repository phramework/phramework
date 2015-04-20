<?php

namespace Phramework\API\extensions;
use Phramework\API\API;

class step_callback {
    
     /**
     * Step callbacks
     */
    const STEP_AFTER_AUTHENTICATION_CHECK = 'STEP_AFTER_AUTHENTICATION_CHECK';
    const STEP_BEFORE_REQUIRE_CONTROLLER = 'STEP_BEFORE_REQUIRE_CONTROLLER';
    const STEP_BEFORE_CALL_METHOD = 'STEP_BEFORE_CALL_METHOD';
    const STEP_BEFORE_CLOSE = 'STEP_BEFORE_CLOSE';

    /**
     * Hold all step callbacks
     * @var array Array of arrays
     */
    protected $step_callback;

    /**
     * Add a step callback
     *
     * Step callbacks, are callbacks that executed when the API reaches
     * a certain step, multiple callbacks can be set for the same step.
     * @param string $step
     * @param function $callback
     * @since 0.1.1
     * @throws \Exception callback_is_not_function_exception
     */
    public function add($step, $callback) {
        
        //Check if step is allowed
        \Phramework\API\models\validate::enum($step, [
            self::STEP_BEFORE_REQUIRE_CONTROLLER,
            self::STEP_BEFORE_CALL_METHOD,
            self::STEP_BEFORE_CLOSE,
        ]);
        
        if (!is_callable($callback)) {
            throw new \Exception(
                API::get_translated('callback_is_not_function_exception'));
        }
        
        //If step_callback list is empty
        if (!isset($this->step_callback[$step])) {
            //Initialize list
            $this->step_callback[$step] = [];
        }

        //Push
        $this->step_callback[$step][] = $callback;
    }

    /**
     * Execute all callbacks set for this step
     * @param string $step
     */
    public function call($step) {
        if (!isset($this->step_callback[$step])) {
            return NULL;
        }
        foreach ($this->step_callback[$step] as $s) {
            if (!is_callable($s)) {
                throw new \Exception(
                    API::get_translated('callback_is_not_function_exception'));
            }
            return $s();
        }
    }
    
    public function __contstruct(){
        $this->step_callback = [];
    }
}