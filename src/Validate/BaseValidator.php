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
 * Description of BaseValidator
 *
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @since 1.0.0
 */
class BaseValidator
{
    /**
     * Validator's type
     * @var string
     */
    protected static $type = 'string';
    
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
     * @var string[]
     */
    protected static $typeAttributes = [];

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
        if (!array_key_exists($key, $this->attributes)) {
            throw new \Exception('Unknown key');
        }

        return $this->attributes[$key];
    }

    /**
     * Set attribute's value
     * @param string $key   Attribute's key
     * @param mixed $value  Attribute's value
     * @throws \Exception If key not found
     */
    public function __set($key, $value)
    {
        if (!array_key_exists($key, $this->attributes)) {
            throw new \Exception('Unknown key');
        }

        $this->attributes[$key] = $value;
    }

    /**
     * Create validator from validation object
     * @param  \stdClass $object Validation object
     * @return BaseValidator
     * @todo use $isFromBase to initialize Validator by name
     */
    public static function fromObject($object)
    {
        $isFromBase = (static::class === self::class);

        //test type if set
        if (isset($object->type) && $object->type !== static::$type) {
            throw new \Exception('Incorrect type');
        }

        $class = new static();

        foreach (static::getTypeAttributes() as $attribute) {
            if (isset($object->{$attribute})) {
                $class->{$attribute} = $object->{$attribute};
            }
        }

        return $class;
    }

    /**
     * Create validator from validation array
     * @param  array $object Validation array
     * @return BaseValidator
     */
    public static function fromArray($array)
    {
        $object = (object)($array);
        return static::fromObject($object);

    }

    /**
     * Create validator from validation object encoded as json object
     * @param  string $object Validation json encoded object
     * @return BaseValidator
     */
    public static function fromJSON($json)
    {
        $object = json_decode($json);
        return static::fromObject($object);
    }

    /**
     * Export validator to json encoded string
     * @return string
     */
    public function toJSON()
    {
        $object = $this->toArray();
        return json_encode($object);
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
        foreach (static::getTypeAttributes() as $attribute) {
            $value = $this->{$attribute};
            if ($value !== null) {
                $object[$attribute] = $value;
            }
        }
        return $object;
    }
}
