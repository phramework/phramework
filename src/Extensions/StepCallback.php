<?php
/**
 * Copyright 2015 Spafaridis Xenofon
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Phramework\Extensions;

use Phramework\Phramework;

/**
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @todo add variables to be passed on call (globaly set)
 */
class StepCallback
{
    /**
     * Step callbacks
     */

    /**
     * Callback has $step, $params, $method, $headers, $callbackVariables
     */
    const STEP_BEFORE_AUTHENTICATION_CHECK = 'STEP_BEFORE_AUTHENTICATION_CHECK';
    /**
     * Callback has $step, $params, $method, $headers, $callbackVariables
     */
    const STEP_AFTER_AUTHENTICATION_CHECK = 'STEP_AFTER_AUTHENTICATION_CHECK';
    /**
     * Called before URIStrategy invocation
     * Callback has $step, $params, $method, $headers, $callbackVariables
     */
    const STEP_BEFORE_CALL_URISTRATEGY = 'STEP_BEFORE_CALL_URISTRATEGY';
    /**
     * Called after URIStrategy invocation
     * Callback has $step, $params, $method, $headers, $callbackVariables, $invokedController, $invokedMethod
     */
    const STEP_AFTER_CALL_URISTRATEGY = 'STEP_AFTER_CALL_URISTRATEGY';
    /**
     * Callback has $step, $params, $method, $headers, $callbackVariables
     */
    const STEP_BEFORE_CLOSE = 'STEP_BEFORE_CLOSE';
    /**
     * Callback has $step, $params, $method, $headers, $callbackVariables
     */
    const STEP_FINALLY = 'STEP_FINALLY';

    /**
     * Hold all step callbacks
     * @var array Array of arrays
     */
    protected $stepCallback;

    protected $variables = [];

    /**
     * Add a valiable to callback variables, passed to callback as parameter
     * @param string $key
     * @param mixed  $variable
     */
    public function addVariable($key, $variable)
    {
        $this->variables[$key] = $variable;
    }

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
    public function add($step, $callback)
    {
        //Check if step is allowed
        \Phramework\Validate\Validate::enum($step, [
            self::STEP_BEFORE_AUTHENTICATION_CHECK,
            self::STEP_AFTER_AUTHENTICATION_CHECK,
            self::STEP_AFTER_CALL_URISTRATEGY,
            self::STEP_BEFORE_CALL_URISTRATEGY,
            self::STEP_BEFORE_CLOSE,
            self::STEP_FINALLY
        ]);

        if (!is_callable($callback)) {
            throw new \Exception(
                Phramework::getTranslated('Callback is not callable')
            );
        }

        //If stepCallback list is empty
        if (!isset($this->stepCallback[$step])) {
            //Initialize list
            $this->stepCallback[$step] = [];
        }

        //Push
        $this->stepCallback[$step][] = $callback;
    }

    /**
     * Execute all callbacks set for this step
     * The value of `$params` and `$headers` can be passed by reference
     * so the callback functions can modify these variables
     * @param string $step
     * @param array  $params  Request parameters
     * @param string $method  Request method
     * @param array  $headers Request headers
     */
    public function call(
        $step,
        &$params = null,
        $method = null,
        &$headers = null,
        $extra = []
    ) {
        if (!isset($this->stepCallback[$step])) {
            return null;
        }

        foreach ($this->stepCallback[$step] as $callback) {
            call_user_func_array(
                $callback,
                array_merge(
                    [
                        $step,
                        &$params,
                        $method,
                        &$headers,
                        $this->variables
                    ],
                    $extra
                )
            );
        }
    }

    public function __contstruct()
    {
        $this->stepCallback = [];
    }
}
