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
use \Phramework\Exceptions\IncorrectParametersException;

/**
 * Datetime validator
 * @uses \Phramework\Validate\String As base implementation's rules to
 * validate that the value is a number and then applies additional rules
 * @property integer $minLength Minimum number of its characters, default is 0
 * @property integer|null $maxLength Maximum number of its characters
 * @see http://json-schema.org/latest/json-schema-validation.html#anchor13
 * *5.1.  Validation keywords for numeric instances (number and integer)*
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @since 1.0.0
 */
class Datetime extends \Phramework\Validate\String
{
    /**
     * Overwrite base class type
     * @var string
     */
    protected static $type = 'datetime';

    /**
     * @todo add options for only date, or only time
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Validate value, validates as SQL date or SQL datetime
     * @see \Phramework\Validate\ValidateResult for ValidateResult object
     * @see https://dev.mysql.com/doc/refman/5.1/en/datetime.html
     * @param  mixed $value Value to validate
     * @return ValidateResult
     * @todo set errorObject
     */
    public function validate($value)
    {
        //Use string's validator
        $return = parent::validate($value);

        //Apply additional rules
        if ($return->status == true && (preg_match(
            '/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/',
            $value,
            $matches
        )) ) {
            if (checkdate($matches[2], $matches[3], $matches[1])) {
                $return->errorObject = null;
                //Set status to success
                $return->status = true;
            } else {
                goto err;
            }
        } else {
            goto err;
        }

        return $return;

        err:
        $return->errorObject = new IncorrectParametersException([
            [
                'type' => static::getType(),
                'failure' => 'format'
            ]
        ]);
        return $return;
    }
}
