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
use \Phramework\Validate\ValidateStatus;

//require __DIR__ . '/IPrimitive.php';

/**
 * @property integer|null minimun
 * @property integer|null maximum
 * @property integer|null exclusiveMinimum
 * @property integer|null exclusiveMaximum
 * @property integer multipleOf
 * @see http://json-schema.org/latest/json-schema-validation.html#anchor13
 * *5.1.  Validation keywords for numeric instances (number and integer)*
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @since 1.0.0
 */
class Integer extends \Phramework\Validate\BaseValidator implements \Phramework\Validate\IPrimitive
{
    /**
     * Overwrite base class attributes
     * @var array
     */
    protected static $typeAttributes = [
        'minimum',
        'maximum',
        'exclusiveMinimum',
        'exclusiveMaximum',
        'multipleOf'
    ];

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
        parent::__construct();

        $this->minimum = $minimum;
        $this->maximum = $maximum;
        $this->exclusiveMinimum = $exclusiveMinimum;
        $this->exclusiveMaximum = $exclusiveMaximum;
        $this->multipleOf = $multipleOf;
    }

    /**
     * Validate value
     * @see \Phramework\Validate\ValidateStatus for ValidateStatus object
     * @param  mixed $value Value to validate
     * @return ValidateStatus
     */
    public function validate($value)
    {
        $return = new ValidateStatus($value, false);

        //Apply all rules

        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            //error
        } elseif ($this->maximum !== null && $value > $this->maximum) {
            //error
        } elseif ($this->exclusiveMaximum !== null
            && $value >= $this->exclusiveMaximum
        ) {
            //error
        } elseif ($this->minimum !== null && $value < $this->minimum) {
            //error
        } elseif ($this->exclusiveMinimum !== null
            && $value <= $this->exclusiveMinimum
        ) {
            //error
        } elseif ($this->multipleOf !== null
            && ((int)$value % $this->multipleOf) !== 0
        ) {
            //error
        } else {
            $return->returnObject = null;
            //Set status to success
            $return->status = true;
            //Type cast
            $return->value  = (int)($value);
        }

        return $return;
    }
}
