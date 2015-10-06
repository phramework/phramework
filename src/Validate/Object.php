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

use \Phramework\Validate\BaseValidator;
use \Phramework\Validate\ValidateResult;
use \Phramework\Models\Filter;

/**
 * Object validator
 * @property integer minProperties Default is 0
 * @property integer|null maxProperties
 * @property array required, Default is []
 * @property object|boolean additionalProperties
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @see http://json-schema.org/latest/json-schema-validation.html#anchor53
 * 5.4.  Validation keywords for objects
 * @since 1.0.0
 */
class Object extends BaseValidator implements \Phramework\Validate\IPrimitive
{
    /**
     * Overwrite base class type
     * @var string
     */
    protected static $type = 'object';

    protected static $typeAttributes = [
        'minProperties',
        'maxProperties',
        'required',
        'properties',
        'additionalProperties',
        'patternProperties',
        'dependencies'
    ];

    public function __construct(
        $minProperties = 0,
        $maxProperties = null,
        $required = [],
        $properties = [],
        $additionalProperties = null,
        $patternProperties = null
    ) {
        parent::__construct();

        $this->minProperties = $minProperties;
        $this->maxProperties = $maxProperties;
        $this->required = $required;
        $this->properties = $properties;
        $this->additionalProperties = $additionalProperties;
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

        if (!is_object($value) && is_array($value)) {
            $value = (object)($value);
        }

        if (!is_object($value)) {
            //error
            goto err;
        }

        $properties = get_object_vars($value);

        $propertiesCount = count($properties);

        if ($propertiesCount < $this->minProperties) {
            //error
            goto err;
        } elseif ($this->maxProperties !== null
            && $propertiesCount > $this->maxProperties
        ) {
            //error
            goto err;
        }

        if ($this->required !== null || !empty($this->required)) {
            //find missing properties
            $missingProperties = [];

            foreach ($this->required as $property) {
                if (!array_key_exists($property, $value)) {
                    //push to missing
                    $missingProperties[] = $property;
                }
            }

            if (!empty($missingProperties)) {
                //error, missing properties
                goto err;
            }
        }

        //success
        $return->status = true;

        //Apply type casted
        $return->value = $value;


        err:
        return $return;
    }

    /**
     * Add properties to this object validator
     * @param array $properties [description]
     * @throws \Exception If properties is not an array
     */
    public function addProperties($properties)
    {
        if (!is_array($properties)) {
            throw new \Exception('Expected array');
        }

        foreach ($properties as $key => $property) {
            $this->addProperty($key, $property);
        }
    }

    /**
     * Add a property to this object validator
     * @param BaseValidator $property
     * @throws \Exception If property key exists
     */
    public function addProperty($key, BaseValidator $property)
    {
        if (array_key_exists($key, $this->properties)) {
            throw new \Exception('Property key exists');
        }

        $this->properties += [$key => $property];
    }


}
