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
 * RequestException
 * Used to throw an \Exception, when there is something wrong with the request.
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @todo cleanup codes
 */
class RequestException extends \Exception
{
    /**
     *
     * @param array $message \Exception message
     * @param integer $code Error code, Optional default 400
     */
    public function __construct($message, $code = 400)
    {
        //Known error codes
        $errors = [];
        if (isset($errors[$code])) {
            $message = $errors[$code];
        }
        parent::__construct($message, $code);
    }
}
