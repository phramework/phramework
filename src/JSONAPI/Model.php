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
namespace Phramework\JSONAPI;

use \Phramework\Phramework;
use \Phramework\JSONAPI\Relationship;

/**
 * Base JSONAPI Model
 */
class Model
{
    /**
     * Model's method prefix
     */
    const GET_RELATIONSHIP_BY_PREFIX = 'getRelationshipBy';
    /**
     * Model's method prefix
     */
    const GET_BY_PREFIX = 'getBy';
    /**
     * Resource's type, used to describe resource objects that share
     * common attributes and relationships
     * **Must** be overwriten
     * @var string
     */
    protected static $type = null;

    /**
     * Resource's table name
     * Can be overwriten, default is null (no database)
     * @var string|null
     */
    protected static $table = null;

    /**
     * Resource's table's schema name
     * Can be overwriten, default is null (no schema)
     * @var string|null
     */
    protected static $schema = null;

    /**
     * Resource's identification attribute (Primary key in database).
     * Can be overwriten, default is id
     * @var string
     */
    protected static $idAttribute = 'id';

    /**
     * Resource's endpoint, usually it the same as type
     * **Must** be overwriten
     * @var string
     */
    protected static $endpoint = null;

    /**
     * Records's type casting schema for database rectods
     * Can be overwriten
     * Also it can be set to empty array to disable type
     * casting for this resource.
     * @var array|null
     */
    protected static $cast = null;

    /**
     * Records's type casting schema
     *
     * This object contains the rules applied to fetched data from database in
     * order to have correct data types.
     * @uses static::$cast If cast is not null
     * @uses static::getValidationModel If static::$cast is null, it uses
     * validationModel's attributes to extract the cast chema
     * @return array
     */
    public static function getCast()
    {
        //If cast is not null
        if (static::$cast != null) {
            return static::$cast;
        }

        //Use validationModel's attributes to extract the cast chema
        if (($validationModel = static::getValidationModel()) != []) {
            $cast = [];

            foreach ($validationModel as $key => $attribute) {
                if (isset($attribute['type'])) {
                    //Push to cast array
                    $cast[$key] = $attribute['type'];
                }
            }

            return $cast;
        }

        return [];
    }

    /**
     * Get resource's type
     * @return string
     */
    public static function getType()
    {
        return static::$type;
    }

    /**
     * Get resource's table name
     * @return string|null
     */
    public static function getTable()
    {
        return static::$table;
    }

    /**
     * Get resource's table schema name
     * @return string
     */
    public static function getSchema()
    {
        return static::$schema;
    }

    /**
     * Resource's identification attribute (Primary key in database)
     * @return string
     */
    public static function getIdAttribute()
    {
        return static::$idAttribute;
    }

    /**
     * Get resource's endpoint
     * @return string
     */
    public static function getEndpoint()
    {
        return static::$endpoint;
    }

    /**
     * Get link to resource's self
     * @param  string $append [description]
     * @return string
     * @uses Phramework::getSetting
     */
    public static function getSelfLink($append = '')
    {
        return Phramework::getSetting('base') . static::getEndpoint() . '/' . $append;
    }

    /**
     * Get resource's relationships
     * @return Relationship[]
     */
    public static function getRelationships()
    {
        return [];
    }

    /**
     * Check if relationship exists
     * @param  string $relationship Relationship's key (alias)
     * @return Boolean
     */
    public static function relationshipExists($relationshipKey)
    {
        $relationships = static::getRelationships();

        return isset($relationships[$relationshipKey]);
    }

    /**
     * Get resource's validation model
     * @return array[]
     */
    public static function getValidationModel()
    {
        return [];
    }

    /**
     * Use resource's validationModel to validate attributes
     *
     * Filtered and fixed values will be updated on original $attributes
     * argument
     * @param array $attributes [description]
     * @uses \Phramework\Validate\Validate
     */
    public static function validate(&$attributes)
    {
        \Phramework\Validate\Validate::model(
            $attributes,
            static::getValidationModel()
        );
    }

    /**
     * Prepare a collection of resources
     * @param  array[]|\stdClass[] $records Multiple records fetched from database
     * @return \stdClass[]
     */
    public static function collection($records = [])
    {
        if (!$records) {
            return [];
        }

        $collection = [];

        foreach ($records as $record) {
            //Convert this record to resource object
            $resource = static::resource($record);

            //attach links.safe to this resource
            if ($resource) {
                //Inlude links object
                $resource->links = [
                    'self' => static::getSelfLink($resource->id)
                ];

                //Push to collection
                $collection[] = $resource;
            }

        }

        return $collection;
    }

    /**
     * Prepare an individual resource
     * @param  array|\stdClass $record An individual record fetched from database
     * @return \stdClass|null
     */
    public static function resource($record)
    {
        if (!$record) {
            return null;
        }

        if (is_array($record)) {
            $record = (object)$record;
        }

        $resource = new \stdClass();

        $resource->type = static::getType();
        $resource->id   = (string)$record->{static::getIdAttribute()};

        //Initialize attributes object (used for represantation order)
        $resource->attributes = (object)[];

        //Attach relationships if resource's relationships are set
        if (($relationships = static::getRelationships())) {
            //Initialize relationships object
            $resource->relationships = [];

            //Parse relationships
            foreach ($relationships as $relationship => $relationshipObject) {
                //Initialize an new relationship entry object
                $relationshipEntry = new \stdClass();

                //Set relationship links
                $relationshipEntry->links = [
                    'self' => static::getSelfLink(
                        $resource->id . '/relationships/' . $relationship
                    ),
                    'related' => static::getSelfLink(
                        $resource->id . '/' . $relationship
                    )
                ];

                $attribute = $relationshipObject->getAttribute();
                $relationshipType = $relationshipObject->getRelationshipType();
                $type = $relationshipObject->getType();

                if (isset($record[$attribute]) && $record[$attribute]) {
                    //If relationship data exists in record's attributes use them

                    //In case of TYPE_TO_ONE attach single object to data
                    if ($relationshipType == Relationship::TYPE_TO_ONE) {
                        $relationshipEntry->data = (object)[
                            'id' => (string)$record[$attribute],
                            'type' => $type
                        ];

                    //In case of TYPE_TO_MANY attach an array of objects
                    } elseif ($relationshipType == Relationship::TYPE_TO_MANY) {
                        $relationshipEntry->data = [];

                        foreach ($record[$attribute] as $k => $d) {
                            //Push object
                            $relationshipEntry->data[] = (object)[
                                'id' => (string)$d,
                                'type' => $type
                            ];
                        }
                    }
                } else {
                    //Else try to use realtionship's class method to retrieve data
                    if ($relationshipType == Relationship::TYPE_TO_MANY) {
                        $callMethod = [
                            $relationshipObject->getRelationshipClass(),
                            self::GET_RELATIONSHIP_BY_PREFIX . ucfirst($resource->type)
                        ];
                        //Check if method exists
                        if (is_callable($callMethod)) {
                            $relationshipEntry->data = [];

                            $relationshipEntryData = call_user_func(
                                $callMethod,
                                $resource->id
                            );

                            foreach ($relationshipEntryData as $k => $d) {
                                //Push object
                                $relationshipEntry->data[] = (object)[
                                    'id' => (string)$d,
                                    'type' => $type
                                ];
                            }
                        }
                    }
                }

                //Unset this attribute (MUST not be visible in resource's attibutes)
                unset($record[$attribute]);

                //Push reletionship to relationships
                $resource->relationships[$relationship] = $relationshipEntry;
            }
        }

        //Attach resource attributes
        $resource->attributes = (object)$record;

        //Return final resource object
        return $resource;
    }

    /**
     * Create record in database
     * @param  array $attributes [description]
     * @param  [type] $return   [description]
     * @return [type]           [description]
     */
    public static function post(
        $attributes,
        $return = \Phramework\Models\SCRUD\Create::RETURN_ID
    ) {
        return \Phramework\Models\SCRUD\Create::create(
            $attributes,
            static::getTable(),
            static::getSchema(),
            $return
        );
    }

    /**
     * Get records from a relationship link
     * @param  static $relationshipKey  [description]
     * @param  string $idAttributeValue [description]
     * @return [type]                   [description]
     * @throws \Phramework\Exceptions\Server If relationship doesn't exist
     * @throws \Phramework\Exceptions\Server If relationship's class method is
     * not defined
     * @throws \Phramework\Exceptions\Server If resources's class
     * `self::GET_RELATIONSHIP_BY_PREFIX . ucfirst(idAttribute)` method isn't
     * defined
     */
    public static function getRelationshipData(
        $relationshipKey,
        $idAttributeValue
    ) {
        if (!static::relationshipExists($relationshipKey)) {
            throw new \Phramework\Exceptions\Server(
                'Not a valid relationship key'
            );
        }

        $relationship = static::getRelationships()[$relationshipKey];

        switch ($relationship->getRelationshipType()) {
            case Relationship::TYPE_TO_ONE:
                $callMethod = [
                    static::class,
                    self::GET_BY_PREFIX . ucfirst(static::getIdAttribute())
                ];

                if (!is_callable($callMethod)) {
                    throw new \Phramework\Exceptions\Server(
                        $callMethod[0] . '::' . $callMethod[1]
                        . ' is not implemented'
                    );
                }

                //We have to get this type's resource
                $resource = call_user_func(
                    $callMethod,
                    $idAttributeValue
                );

                if (!$resource) {
                    return null;
                }
                //And use it's relationships data for this relationship
                return $resource->relationships[$relationshipKey]->data;

                break;
            case Relationship::TYPE_TO_MANY:
            default:
                $callMethod = [
                    $relationship->getRelationshipClass(),
                    self::GET_RELATIONSHIP_BY_PREFIX . ucfirst(static::getType())
                ];

                if (!is_callable($callMethod)) {
                    throw new \Phramework\Exceptions\Server(
                        $callMethod[0] . '::' . $callMethod[1]
                        . ' is not implemented'
                    );
                }
                //also we could attempt to use GetById like the above TO_ONE
                //to use relationships data

                return call_user_func(
                    $callMethod,
                    $idAttributeValue
                );
                break;
        }
    }
}
