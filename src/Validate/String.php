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
use \Phramework\Models\Filter;

/**
 * String validator
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @since 1.0.0
 */
class String extends \Phramework\Validate\BaseValidator implements \Phramework\Validate\IPrimitive
{
    /**
     * Overwrite base class type
     * @var string
     */
    protected static $type = 'string';

    protected static $typeAttributes = [
        'minLength',
        'maxLength',
        'pattern',
        'raw'
    ];

    public function __construct(
        $minLength = 0,
        $maxLength = null,
        $pattern = null,
        $raw = false
    ) {
        parent::__construct();

        $this->minLength = $minLength;
        $this->maxLength = $maxLength;
        $this->pattern = $pattern;
        $this->raw = $raw;
    }

    /**
     * Validate value
     * @see \Phramework\Validate\ValidateResult for ValidateResult object
     * @param  mixed $value Value to validate
     * @return ValidateResult
     * @uses https://secure.php.net/manual/en/function.is-string.php
     * @uses filter_var with FILTER_VALIDATE_REGEXP for pattern
     */
    public function validate($value)
    {
        $return = new ValidateResult($value, false);

        if (!is_string($value)) {
            //error
        } elseif (mb_strlen($value) < $this->minLength) {
            //error
        } elseif ($this->maxLength !== null
            && mb_strlen($value) > $this->maxLength
        ) {
            //error
        } elseif ($this->pattern !== null
            && filter_var($value, FILTER_VALIDATE_REGEXP, [
                'options' => ['regexp' => $this->pattern]
            ]) === false
        ) {
            //error
        } else {
            $return->errorObject = null;
            //Set status to success
            $return->status = true;

            if ($this->raw) {
                //use raw
                $return->value  = $value;
            } else {
                //or filter
                $return->value = strip_tags(Filter::string($value));
            }
        }

        return $return;
    }
}
