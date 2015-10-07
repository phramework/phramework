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
 * Object validator
 * @property integer minProperties Default is 0
 * @property integer|null maxProperties
 * @property array required, Default is []
 * @property BaseValidator[] properties, Default is []
 * @property object|boolean additionalProperties
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @see http://json-schema.org/latest/json-schema-validation.html#anchor53
 * 5.4.  Validation keywords for objects
 * @since 1.0.0
 * @todo Implement patternProperties
 * @todo Implement additionalProperties
 * @todo Implement dependencies
 * @todo Can it have default?
 * @todo Check if required property is set in properties
 */
class Object extends \Phramework\Validate\BaseValidator
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
        $properties = [],
        $required = [],
        $additionalProperties = null,
        $minProperties = 0,
        $maxProperties = null,
        $patternProperties = null
    ) {
        parent::__construct();

        $this->minProperties = $minProperties;
        $this->maxProperties = $maxProperties;
        $this->properties = $properties;
        $this->required = $required;
        $this->additionalProperties = $additionalProperties;
    }

    /**
     * Validate value
     * @see \Phramework\Validate\ValidateResult for ValidateResult object
     * @param  mixed $value Value to validate
     * @return ValidateResult
     * @todo if array, remove elements without keys
     */
    public function validate($value)
    {
        $return = new ValidateResult($value, false);

        if (!is_object($value) && is_array($value)) {
            $value = (object)($value);
        }

        if (!is_object($value)) {
            $return->errorObject = 'Not an object or array';
            //error
            goto err;
        }

        $valueProperties = get_object_vars($value);

        $valuePropertiesCount = count($valueProperties);

        if ($valuePropertiesCount < $this->minProperties) {
            //error
            $return->errorObject = 'minProperties';
            goto err;
        } elseif ($this->maxProperties !== null
            && $valuePropertiesCount > $this->maxProperties
        ) {
            $return->errorObject = 'maxProperties';
            //error
            goto err;
        }

        //Check if required properties are set and find if any of them are missing
        if ($this->required !== null || !empty($this->required)) {
            //Find missing properties
            $missingProperties = [];

            foreach ($this->required as $key) {
                if (!array_key_exists($key, $valueProperties)) {
                    //Push key to missing
                    $missingProperties[] = $key;
                }
            }

            if (!empty($missingProperties)) {
                //error, missing properties
                $return->errorObject = 'required properties';
                goto err;
            }
        }

        $overalPropertyStatus = true;

        //Validate all validator's properties
        foreach ($this->properties as $key => $property) {
            //If this property key exists in given $value, validate it
            if (array_key_exists($key, $valueProperties)) {
                $propertyValue = $valueProperties[$key];
                $propertyValidateResult = $property->validate($propertyValue);

                //Determine $overalPropertyStatus
                $overalPropertyStatus = $overalPropertyStatus && $propertyValidateResult->status;

                //use type casted value
                $value->{$key} = $propertyValidateResult->value;

            } else {
                //Else use default property's value
                $value->{$key} = $property->default;
            }
        }

        if (!$overalPropertyStatus) {
            $return->status = $overalPropertyStatus;
            $return->errorObject = 'properties validation';
            //error
            //todo here we must collect all errorObjects
            goto err;
        }

        //success
        $return->status = true;

        //Apply type casted
        $return->value = $value;

        return $return;

        err:
        return $return;
    }

    /**
     * This method use this validator to parse data from $value argument
     * and return a clean object
     * @param  array|stdClass $value [description]
     * @throws \Phramework\Exceptions\MissingParameters
     * @throws \Phramework\Exceptions\IncorrectParameters
     * @return stdClass        [description]
     * @todo find out if MissingParameters
     * @todo add errors
     */
    public function parse($value) {
        if (is_array($value)) {
            $value = (object)$value;
        }

        $validateResult = $this->validate($value);

        if (!$validateResult->status) {
            //temp hack
            if ($validateResult->errorObject == 'required properties') {
                throw new \Phramework\Exceptions\MissingParameters([]);
            }
            throw new \Phramework\Exceptions\IncorrectParameters([]);
        }

        $castedValue = $validateResult->value;

        return $castedValue;
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

        return $this;
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

        //Add this key, value to
        $this->properties += [$key => $property];

        return $this;
    }
}
