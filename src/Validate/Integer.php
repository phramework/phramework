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
use \Phramework\Validate\ValidateResult;

//require __DIR__ . '/IPrimitive.php';

/**
 * @uses \Phramework\Validate\Number As base implementation's rules to
 * validate that the value is a number and then applies additional rules
 * to validate that this is a interger
 * @property integer|null minimun
 * @property integer|null maximum
 * @property boolean|null exclusiveMinimum
 * @property boolean|null exclusiveMaximum
 * @property integer multipleOf
 * @see http://json-schema.org/latest/json-schema-validation.html#anchor13
 * *5.1.  Validation keywords for numeric instances (number and integer)*
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @since 1.0.0
 */
class Integer extends \Phramework\Validate\Number
{
    /**
     * Overwrite base class type
     * @var string
     */
    protected static $type = 'integer';

    public function __construct(
        $minimum = null,
        $maximum = null,
        $exclusiveMinimum = null,
        $exclusiveMaximum = null,
        $multipleOf = null
    ) {
        parent::__construct(
            $minimum,
            $maximum,
            $exclusiveMinimum,
            $exclusiveMaximum,
            $multipleOf
        );
    }

    /**
     * Validate value
     * @see \Phramework\Validate\ValidateResult for ValidateResult object
     * @param  mixed $value Value to validate
     * @return ValidateResult
     */
    public function validate($value)
    {
        $return = parent::validate($value);

        //Apply additional rules
        if ($return->status == true) {
            if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                //error
                $return->status = false;
            } else {
                $return->errorObject = null;
                //Set status to success
                $return->status = true;
                //Type cast
                $return->value  = (int)($value);
            }
        }

        return $return;
    }
}
