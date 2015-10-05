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
 * @property float|null minimun
 * @property float|null maximum
 * @property float|null exclusiveMinimum
 * @property float|null exclusiveMaximum
 * @property float multipleOf
 * @see http://json-schema.org/latest/json-schema-validation.html#anchor13
 * *5.1.  Validation keywords for numeric instances (number and integer)*
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @since 1.0.0
 */
class Number extends \Phramework\Validate\BaseValidator implements \Phramework\Validate\IPrimitive
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
    protected static $type = 'number';

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
     * @see \Phramework\Validate\ValidateResult for ValidateResult object
     * @param  mixed $value Value to validate
     * @return ValidateResult
     */
    public function validate($value)
    {
        $return = new ValidateResult($value, false);

        if (is_string($value)) {
            //Replace comma with dot
            $value = str_replace(',', '.', $value);
        }
        //Apply all rules

        if (filter_var($value, FILTER_VALIDATE_FLOAT) === false) {
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
            && ((float)$value % $this->multipleOf) !== 0
        ) {
            //error
        } else {
            $return->returnObject = null;
            //Set status to success
            $return->status = true;
            //Type cast
            $return->value  = (float)($value);
        }

        return $return;
    }
}
