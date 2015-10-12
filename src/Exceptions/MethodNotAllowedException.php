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
namespace Phramework\Exceptions;

/**
 * MethodNotAllowedException
 * Used to throw an \Exception, when this method is not allowed
 * to apply to this resource, or the current status of the resource.
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 */
class MethodNotAllowedException extends \Exception
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
        $this->allowedMethods = $allowedMethods;
    }


    public function getAllowedMethods()
    {
        return  $this->allowedMethods;
    }
}
