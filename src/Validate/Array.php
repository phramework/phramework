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
 * Array validator
 * @property object|array $items If it is an object, this object MUST be a valid JSON Schema. If it is an array, items of this array MUST be objects, and each of these objects MUST be a valid JSON Schema.
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @see http://json-schema.org/latest/json-schema-validation.html#anchor36
 * @since 1.0.0
 * @todo Cannot be named Array
 */
class ArrayClass extends \Phramework\Validate\BaseValidator implements \Phramework\Validate\IPrimitive
{
    /**
     * Overwrite base class type
     * @var string
     */
    protected static $type = 'array';

    protected static $typeAttributes = [
        'minItems',
        'maxItems',
        'additionalItems',
        'items',
        'uniqueItems'
    ];

}
