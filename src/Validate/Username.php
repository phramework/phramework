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
 * Username validator
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
class Username extends \Phramework\Validate\String
{
    /**
     * Overwrite base class type
     * @var string
     */
    protected static $type = 'username';

    protected static $usernamePattern = '/^[A-Za-z0-9_\.]{3,32}$/';

    public static function setUsernamePattern($pattern)
    {
        static::$usernamePattern = $pattern;
    }

    public static function getUsernamePattern()
    {
        return static::$usernamePattern;
    }

    public function __construct(
        $minLength = 0,
        $maxLength = null
    ) {
        parent::__construct(
            $minLength,
            $maxLength,
            static::getUsernamePattern()
        );
    }
}
