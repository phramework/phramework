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

/**
 * BaseValidator, every validator **MUST** extend this class
 *
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @since 1.0.0
 */
abstract class BaseValidator
{
    /**
     * Validator's type
     * Must be overwriten, default is 'string'
     * @var string
     */
    protected static $type = 'string';

    /**
     * This static method will instanciate a new object as validation model
     * to parse the input value
     * @param mixed $value Input value to validate
     */
    public static function parseStatic($value)
    {
        $validationObject = new static();

        return $validationObject->parse($value);
    }

    /**
    * Validate value
    * @see \Phramework\Validate\ValidateResult for ValidateResult object
    * @param  mixed $value Input value to validate
    * @return ValidateResult
     */
    abstract public function validate($value);

    /**
     * Get validator's type
     * @return string
     */
    public static function getType()
    {
        return static::$type;
    }

    /**
     * Validator's attributes
     * Can be overwriten
     * @var string[]
     */
    protected static $typeAttributes = [
    ];

    /**
     * Common valdator attributes
     * @var string[]
     */
    protected static $commonAttributes = [
        'title',
        'description',
        'default',
        'format'
    ];

    public $default;

    /**
     * Get validator's attributes
     * @return string[]
     */
    public static function getTypeAttributes()
    {
        return static::$typeAttributes;
    }

    /**
     * Objects current attributes and values
     * @var array
     */
    protected $attributes = [];

    protected function __construct()
    {
        //Append common attributes
        foreach (static::$commonAttributes as $attribute) {
            $this->attributes[$attribute] = null;
        }

        //Append type attributes
        foreach (static::$typeAttributes as $attribute) {
            $this->attributes[$attribute] = null;
        }
    }

    /**
     * Get attribute's value
     * @param  string $key Attribute's key
     * @return mixed
     * @throws \Exception If key not found
     */
    public function __get($key)
    {
        if ($key === 'type') {
            return $this->getType();
        }

        if (!array_key_exists($key, $this->attributes)) {
            throw new \Exception('Unknown key "' . $key . '" to get');
        }

        return $this->attributes[$key];
    }

    /**
     * Set attribute's value
     * @param string $key   Attribute's key
     * @param mixed $value  Attribute's value
     * @throws \Exception If key not found
     * @return BaseValidator Return's this validator object
     */
    public function __set($key, $value)
    {
        if (!array_key_exists($key, $this->attributes)) {
            throw new \Exception(sprintf(
                'Unknown key "%s" to set',
                $key
            ));
        }

        /*if ($key == 'properties' && is_array($value)) {
            $value = (object)$value;
        }*/

        $this->attributes[$key] = $value;

        return $this;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function setDescription($description)
    {
        return $this->__set('description', $description);
    }

    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * This method use this validator to parse data from $value argument
     * and return a clean object
     * @param  mixed $value Input value to validate
     * @throws \Phramework\Exceptions\MissingParametersException
     * @throws \Phramework\Exceptions\IncorrectParametersException
     * @return mixed
     */
    public function parse($value)
    {
        $validateResult = $this->validate($value);

        if (!$validateResult->status) {
            //temp hack
            /*if ($validateResult->errorObject == 'required properties') {
                throw new \Phramework\Exceptions\MissingParametersException([]);
            }
            throw new \Phramework\Exceptions\IncorrectParametersException([]);
            */
            throw $validateResult->errorObject;
        }

        $castedValue = $validateResult->value;

        return $castedValue;
    }

    /**
     * Create validator from validation object
     * @param  \stdClass $object Validation object
     * @return BaseValidator
     */
    public static function createFromObject($object)
    {
        $isFromBase = (static::class === self::class);

        //Test type if it's set
        if (property_exists($object, 'type')) {// && $object->type !== static::$type) {
            if (class_exists(__NAMESPACE__ . '\\' . $object->type)) {
                //if already loaded
                $className = __NAMESPACE__ . '\\' . $object->type;
                $class = new $className();
            } elseif ($object->type == 'array') {
                $class = new ArrayValidator();
            } elseif ($object->type == 'url') {
                $class = new URL();
            } elseif ($object->type == 'unsignedinteger') {
                $class = new UnsignedInteger();
            } elseif (file_exists(__DIR__ . '/' . ucfirst($object->type) . '.php')) {
                $className = __NAMESPACE__ . '\\' . ucfirst($object->type);
                $class = new $className();
            } else {
                $className = $object->type;

                try {
                    $ref = new \ReflectionClass($className);
                    $class = new $className();
                } catch (\Exception $e) {
                    //Wont catch the fatal error
                    throw new \Exception(sprintf(
                        'Incorrect type %s from %s',
                        $object->type,
                        static::class
                    ));
                }
            }
        } elseif (!$isFromBase || $object->type == static::$type) {
            $class = new static();
        } else {
            throw new \Exception(sprintf(
                'Type is required when creating from "%s"',
                self::class
            ));
        }

        //For each Validator's attribute
        foreach (array_merge($class::getTypeAttributes(), $class::$commonAttributes) as $attribute) {
            //Check if provided object contains this attribute
            if (property_exists($object, $attribute)) {
                if ($attribute == 'properties') {
                    //get properties as array
                    $properties = $object->{$attribute};

                    $createdProperties = new \stdClass();

                    foreach ($properties as $key => $property) {
                        if (!is_object($property)) {
                            throw new \Exception(sprintf(
                                'Expected object for property value %',
                                $key
                            ));
                        }

                        $createdProperties->{$key} =
                        BaseValidator::createFromObject($property);
                    }
                    //push to class
                    $class->{$attribute} = $createdProperties;
                } else {
                    //Use attributes value in Validator object
                    $class->{$attribute} = $object->{$attribute};
                }
            }
        }

        return $class;
    }

    /**
     * Create validator from validation array
     * @param  array $object Validation array
     * @return BaseValidator
     */
    public static function createFromArray($array)
    {
        $object = (object)($array);
        return static::createFromObject($object);

    }

    /**
     * Create validator from validation object encoded as json object
     * @param  string $object Validation json encoded object
     * @return BaseValidator
     */
    public static function createFromJSON($json)
    {
        $object = json_decode($json);
        return static::createFromObject($object);
    }

    /**
     * Export validator to json encoded string
     * @return string
     */
    public function toJSON($JSON_PRETTY_PRINT = false)
    {
        $object = $this->toArray();
        return json_encode(
            $object,
            ($JSON_PRETTY_PRINT ? JSON_PRETTY_PRINT : 0)
        );
    }

    /**
     * Export validator to json encoded string
     * @return \stdClass
     */
    public function toObject()
    {
        $object = $this->toArray();
        return (object)$object;
    }

    /**
     * Export validator to json encoded string
     * @return array
     */
    public function toArray()
    {
        $object = ['type' => static::$type];
        foreach (array_merge(
            static::getTypeAttributes(),
            static::$commonAttributes
        ) as $attribute) {
            $value = $this->{$attribute};
            if ($value !== null) {
                $object[$attribute] = $value;
            }
            if (static::$type == 'object' && $attribute == 'properties') {
                foreach ($object[$attribute] as $key => $property) {
                    $object[$attribute]->{$key} = $property->toArray();
                }
            }
        }
        return $object;
    }
}
