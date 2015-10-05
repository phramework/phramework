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
namespace Phramework\Validate;

use \Phramework\Exceptions\IncorrectParameters;

class Integer implements IPrimitive
{
    protected $min = null;
    protected $max = null;

    public function __constructor($min, $max, $required)
    {

    }

    public function validate($value)
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            return new \stdObject();
        }

        if ($this->max !== null && $value > $this->max) {
            //error
        }

        if ($this->min !== null && $value < $this->min) {
            //error
        }

        //Cast datatype
        return (int)($value);
    }
}
