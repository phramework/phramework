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

use \Phramework\Validate\ValidateResult;

/**
 * Enum validator
 * @property array values
 * @see http://json-schema.org/latest/json-schema-validation.html#anchor13
 * *5.1.  Validation keywords for numeric instances (number and integer)*
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @since 1.0.0
 */
class Enum extends \Phramework\Validate\BaseValidator
{
    /**
     * Overwrite base class type
     * @var string
     */
    protected static $type = 'enum';

    /**
     * Overwrite base class attributes
     * @var array
     */
    protected static $typeAttributes = [
        'values',
        'validateType'
    ];

    public function __construct(
        $values,
        $validateType = false,
        $default = null
    ) {
        parent::__construct();

        $this->values  = $values;
        $this->validateType = $validateType;
        $this->default = $default;
    }

    /**
     * Validate value
     * @see \Phramework\Validate\ValidateResult for ValidateResult object
     * @param  mixed $value Value to validate
     * @return ValidateResult
     */
    public function validate($value)
    {
        $return = new ValidateResult($value, false);

        if (is_array($value) || is_object($value)) {
            throw new \Exception('Arrays and objects are not allowed');
        }

        foreach ($this->values as $v) {
            if ($value == $v) {
                if ($this->validateType && gettype($value) !== gettype($v)) {
                    //ignore
                    continue;
                }
                //Success
                //Overwrite $return's value (get correct object type)
                $return->value = $v;
                //Set status to true
                $return->status = true;

                return $return;
            }
        }

        //Rrror
        $return->errorObject = 'Not found';

        return $return;
    }
}
