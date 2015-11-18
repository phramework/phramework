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
use \Phramework\Models\Operator;
use \Phramework\JSONAPI\Relationship;

/**
 * Base JSONAPI Model
 * @package JSONAPI
 * @since 1.0.0
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
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
     * @todo Rewrite validationModel's attributes
     */
    public static function getCast()
    {
        //If cast is not null
        if (static::$cast != null) {
            return static::$cast;
        }

        //Use validationModel's attributes to extract the cast chema
        /*if (($validationModel = static::getValidationModel()) != []) {
            $cast = [];

            foreach ($validationModel as $key => $attribute) {
                if (isset($attribute['type'])) {
                    //Push to cast array
                    $cast[$key] = $attribute['type'];
                }
            }

            return $cast;
        }*/

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

    public static function getRelationship($relationshipKey)
    {
        $relationships = static::getRelationships();

        if (!isset($relationships[$relationshipKey])) {
            throw new \Exception('Not a valid relationship key');
        }

        return $relationships[$relationshipKey];
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
     * @return \Phramework\Validate\Object
     */
    public static function getValidationModel()
    {
        return null;
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

            //Attach links.self to this resource
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

        if (!is_object($record) && is_array($record)) {
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

                if (isset($record->{$attribute}) && $record->{$attribute}) {
                    //If relationship data exists in record's attributes use them

                    //In case of TYPE_TO_ONE attach single object to data
                    if ($relationshipType == Relationship::TYPE_TO_ONE) {
                        $relationshipEntry->data = (object)[
                            'id' => (string)$record->{$attribute},
                            'type' => $type
                        ];

                    //In case of TYPE_TO_MANY attach an array of objects
                    } elseif ($relationshipType == Relationship::TYPE_TO_MANY) {
                        $relationshipEntry->data = [];

                        foreach ($record->{$attribute} as $k => $d) {
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
                unset($record->{$attribute});

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
     * Create a record in database
     * @param  array $attributes
     * @param  \Phramework\Models\SCRUD\Create::RETURN_ID Return type,
     * default is RETURN_ID
     * @return mixed
     * @todo disable post ?
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
     * Update selected attributes of a database record
     * @param  mixex $id id attribute's value
     * @param  array $attributes Key-value array with fields to update
     * @return number of updated rows
     * @todo add query limit
     */
    public static function patch($id, $attributes)
    {
        return \Phramework\Models\SCRUD\Update::update(
            $id,
            (array)$attributes,
            static::getTable(),
            static::getIdAttribute()
        );
    }

    /**
     * Get filterable attributes
     * @return array
     */
    public static function getFilterable()
    {
        return [];
    }

    /**
     * Get attributes that can be updated using PATCH
     * @return array
     */
    public static function getMutable()
    {
        return [];
    }

    /**
     * Get sort attributes and default
     * @return object Returns an object with attribute `attributes` containing
     * an string[] with allowed sort attributes
     * and attribute `default` a string|null having the value of default, boolean `ascending`
     * sorting attribute
     */
    public static function getSort()
    {
        return (object)['attributes' => [], 'default' => null, 'ascending' => true];
    }

    /**
     * Get records from a relationship link
     * @param  static $relationshipKey
     * @param  string $idAttributeValue
     * @return stdClass|stdClass[]
     * @throws \Phramework\Exceptions\ServerException If relationship doesn't exist
     * @throws \Phramework\Exceptions\ServerException If relationship's class method is
     * not defined
     * @throws \Phramework\Exceptions\ServerException If resources's class
     * `self::GET_RELATIONSHIP_BY_PREFIX . ucfirst(idAttribute)` method isn't
     * defined
     */
    public static function getRelationshipData(
        $relationshipKey,
        $idAttributeValue,
        $additionalGetArguments = [],
        $additionalArguments = []
    ) {
        if (!static::relationshipExists($relationshipKey)) {
            throw new \Phramework\Exceptions\ServerException(
                'Not a valid relationship key'
            );
        }

        $relationship = static::getRelationship($relationshipKey);

        switch ($relationship->getRelationshipType()) {
            case Relationship::TYPE_TO_ONE:
                $callMethod = [
                    static::class,
                    self::GET_BY_PREFIX . ucfirst(static::getIdAttribute())
                ];

                if (!is_callable($callMethod)) {
                    throw new \Phramework\Exceptions\ServerException(
                        $callMethod[0] . '::' . $callMethod[1]
                        . ' is not implemented'
                    );
                }

                //We have to get this type's resource
                $resource = call_user_func_array(
                    $callMethod,
                    array_merge([$idAttributeValue], $additionalGetArguments)
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
                    throw new \Phramework\Exceptions\ServerException(
                        $callMethod[0] . '::' . $callMethod[1]
                        . ' is not implemented'
                    );
                }
                //also we could attempt to use GetById like the above TO_ONE
                //to use relationships data

                return call_user_func_array(
                    $callMethod,
                    array_merge([$idAttributeValue], $additionalArguments)
                );
                break;
        }
    }


    /**
     * Get jsonapi's included object, selected by include argument,
     * using id's of relationship's data from resources in primary data object
     * @param  object $primaryData Primary data object
     * @param  string[] $include     An array with the keys of relationships to include
     * @return object[]              An array with all included related data
     * @todo handle Relationship resource cannot be accessed
     */
    public static function getIncludedData(
        $primaryData,
        $include = [],
        $additionalArguments = []
    ) {
        //Store relationshipKeys as key and ids of their related data as value
        $temp = [];

        //check if relationship exists
        foreach ($include as $relationshipKey) {
            if (!static::relationshipExists($relationshipKey)) {
                throw new \Phramework\Exceptions\RequestException(
                    'Include relationship not found'
                );
            }

            //Will hold ids of related data
            $temp[$relationshipKey] = [];
        }

        if (empty($include) || empty($primaryData)) {
            return [];
        }

        //iterate through all primary data

        //if a single resource
        if (!is_array($primaryData)) {
            $primaryData = [$primaryData];
        }

        foreach ($primaryData as $resource) {
            //ignore if relationships are not set
            if (!property_exists($resource, 'relationships')) {
                continue;
            }

            foreach ($include as $relationshipKey) {
                //ignore if this relationship is not set
                if (!isset($resource->relationships[$relationshipKey])) {
                    continue;
                }

                //if single
                $relationshipData = $resource->relationships[$relationshipKey]->data;

                if (!$relationshipData || empty($relationshipData)) {
                    continue;
                }

                //if a single resource
                if (!is_array($relationshipData)) {
                    $relationshipData = [$relationshipData];
                }

                foreach ($relationshipData as $primaryKeyAndType) {
                    //push primary key (use type? $primaryKeyAndType->type)
                    $temp[$relationshipKey][] = $primaryKeyAndType->id;
                }
            }
        }

        $included = [];

        //remove duplicates
        foreach ($include as $relationshipKey) {
            $relationship = static::getRelationship($relationshipKey);

            $callMethod = [
                $relationship->getRelationshipClass(),
                self::GET_BY_PREFIX
                . ucfirst($relationship->getRelationshipIdAttribute())
            ];

            if (!is_callable($callMethod)) {
                throw new \Phramework\Exceptions\ServerException(
                    $callMethod[0] . '::' . $callMethod[1]
                    . ' is not implemented'
                );
            }

            foreach (array_unique($temp[$relationshipKey]) as $idAttribute) {
                $additionalArgument = (
                    isset($additionalArguments[$relationshipKey])
                    ? $additionalArguments[$relationshipKey]
                    : []
                );

                $resource = call_user_func_array(
                    $callMethod,
                    array_merge([$idAttribute], $additionalArgument)
                );

                if (!$resource) {
                    //throw new \Exception('Relationship resource cannot be accessed');
                }

                //push to included
                $included[] = $resource;
            }
        }

        //fetch related resources using GET_BY_PREFIX{{idAttribute}} method

        return $included;
    }

    /**
     * This method will update `{{sort}}` string inside query parameter with
     * the provided sort
     * @param  string       $query    Query
     * @param  null|object  $sort     string `table`, string `attribute`, boolean `ascending`
     * @return string       Query
     */
    protected static function handleSort($query, $sort)
    {
        $replace = '';

        if ($sort) {
            $replace = "\n" . sprintf(
                'ORDER BY "%s"."%s" %s',
                $sort->table,
                $sort->attribute,
                ($sort->ascending ? 'ASC' : 'DESC')
            );
        }

        $query = str_replace(
            '{{sort}}',
            $replace,
            $query
        );

        return $query;
    }

    /**
     * This method will update `{{pagination}}` string inside query parameter with
     * the provided pagination directives
     * @param  string  $query    Query
     * @param  object  $page
     * @return string            Query
     */
    protected static function handlePagination($query, $page = null)
    {
        $additionallPagination = [];

        if ($page !== null) {
            if (isset($page->limit)) {
                $additionallPagination[] = sprintf(
                    'LIMIT %s',
                    $page->limit
                );
            }

            if (isset($page->offset)) {
                $additionallPagination[] = sprintf(
                    'OFFSET %s',
                    $page->offset
                );
            }
        }

        $query = str_replace(
            '{{pagination}}',
            implode("\n", $additionallPagination),
            $query
        );

        return $query;
    }

    /**
     * This method will update `{{filter}}` string inside query parameter with
     * the provided filter directives
     * @param  string  $query    Query
     * @param  object  $filter   This object has 3 attributes:
     * primary, relationships and attributes
     * - integer[] $primary
     * - integer[] $relationships
     * - array $attributes (each array item [$attribute, $operator, $operant])
     * - array $attributesJSON (each array item [$attribute, $key, $operator, $operant])
     * @param  boolean $hasWhere If query already has an WHERE, default is true
     * @return string            Query
     * @todo check if query work both in MySQL and postgre
     */
    protected static function handleFilter(
        $query,
        $filter = null,
        $hasWhere = true
    ) {
        $additionalFilter = [];

        if ($filter && $filter->primary) {
            $additionalFilter[] = sprintf(
                '%s "%s"."%s" IN (%s)',
                ($hasWhere ? 'AND' : 'WHERE'),
                static::$table,
                static::$idAttribute,
                implode(',', $filter->primary)
            );

            $hasWhere = true;
        }

        $relationships = static::getRelationships();

        foreach ($filter->relationships as $key => $value) {
            if (!static::relationshipExists($key)) {
                throw new \Exception(sprintf(
                    'Relationship "%s" not found',
                    $key
                ));
            }
            $relationship = $relationships[$key];
            $relationshipClass = $relationship->getRelationshipClass();

            if ($relationship->getRelationshipType() === Relationship::TYPE_TO_ONE) {
                $additionalFilter[] = sprintf(
                    '%s "%s"."%s" IN (%s)',
                    ($hasWhere ? 'AND' : 'WHERE'),
                    static::$table, //$relationshipclass::getTable(),
                    $relationship->getAttribute(),
                    implode(',', $filter->relationships[$key])
                );
                $hasWhere = true;
            } else {
                throw new \Phramework\Exceptions\NotImplementedException(
                    'Filtering by TYPE_TO_MANY relationships are not implemented'
                );
            }
        }

        foreach ($filter->attributes as $value) {
            list($key, $operator, $operant) = $value;
            if (in_array($operator, Operator::getOrderableOperators())) {
                $additionalFilter[] = sprintf(
                    '%s "%s"."%s" %s %s',
                    ($hasWhere ? 'AND' : 'WHERE'),
                    static::$table,
                    $key,
                    $operator,
                    $operant
                );
            } elseif (in_array($operator, Operator::getNullableOperators())) {
                //Define a transformation matrix, operator to SQL operator
                $transformation = [
                    Operator::OPERATOR_NOT_ISNULL => 'IS NOT NULL'
                ];

                $additionalFilter[] = sprintf(
                    '%s "%s"."%s" %s \'%s\'',
                    ($hasWhere ? 'AND' : 'WHERE'),
                    static::$table,
                    $key,
                    (
                        array_key_exists($operator, $transformation)
                        ? $transformation[$operator]
                        : $operator
                    )
                );
            } elseif (in_array($operator, Operator::getLikeOperators())) {
                //Define a transformation matrix, operator to SQL operator
                $transformation = [
                    Operator::OPERATOR_LIKE => 'LIKE',
                    Operator::OPERATOR_NOT_LIKE => 'NOT LIKE'
                ];

                //LIKE '%text%', force lower - case insensitive
                $additionalFilter[] = sprintf(
                    '%s LOWER("%s"."%s") %s \'%%%s%%\'',
                    ($hasWhere ? 'AND' : 'WHERE'),
                    static::$table,
                    $key,
                    (
                        array_key_exists($operator, $transformation)
                        ? $transformation[$operator]
                        : $operator
                    ),
                    strtolower($operant)
                );
            } else {
                throw new \Phramework\Exceptions\NotImplementedException(sprintf(
                    'Filtering by operator "%s" is not implemented',
                    $operator
                ));
            }

            $hasWhere = true;
        }

        $filterJSON = $filter->attributesJSON;
        //hack.

        foreach ($filterJSON as $value) {
            list($attribute, $key, $operator, $operant) = $value;

            if (in_array($operator, Operator::getOrderableOperators())) {
                $additionalFilter[] = sprintf(
                    '%s "%s"."%s"->>\'%s\' %s \'%s\'',
                    ($hasWhere ? 'AND' : 'WHERE'),
                    static::$table,
                    $attribute,
                    $key,
                    $operator,
                    $operant
                );
            } else {
                throw new \Phramework\Exceptions\NotImplementedException(sprintf(
                    'Filtering JSON by operator "%s" is not implemented',
                    $operator
                ));
            }
            $hasWhere = true;
        }

        $query = str_replace(
            '{{filter}}',
            implode("\n", $additionalFilter),
            $query
        );

        return $query;
    }

    /**
     * Apply handle pagination, sort and filter to query,
     * will replace `{{sort}}`, `{{pagination}}` and `{{filter}}` strings in
     * query.
     * @uses Model::handlePagination
     * @uses Model::handleSort
     * @uses Model::handleFilter
     * @param  string  $query    Query
     * @param  object  $page     See handlePagination $page parameter
     * @param  object  $filter   See handleFilter $filter parameter
     * @param  null|object $sort See handleSort $sort parameter
     * @param  boolean $hasWhere If query already has an WHERE, default is true
     * @return string       Query
     */
    protected static function handleGet(
        $query,
        $page,
        $filter,
        $sort,
        $hasWhere = true
    ) {
        return self::handlePagination(
            self::handleSort(
                self::handleFilter(
                    $query,
                    $filter,
                    $hasWhere
                ),
                $sort
            ),
            $page
        );
    }
}
