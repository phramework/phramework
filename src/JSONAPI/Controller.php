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

use \Phramework\Models\Request;
use \Phramework\Validate\Validate;
use \Phramework\Models\Util;
use \Phramework\Models\Filter;
use \Phramework\Models\Operator;
use \Phramework\Exceptions\RequestException;

/**
 * Base JSONAPI controller
 * @package JSONAPI
 * @since 1.0.0
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 */
class Controller
{
    /**
     * Shortcut to \Phramework\Phramework::view.
     * @uses \Phramework\Phramework::view
     * @param array|object $parameters Response parameters
     * @uses \Phramework\Phramework::view
     */
    protected static function view($parameters = [])
    {
        \Phramework\Phramework::view($parameters);
    }

    /**
     * If !assert then a NotFoundException exceptions is thrown.
     *
     * @param mixed  $assert
     * @param string $exceptionMessage [Optional] Default is
     * 'Resource not found'
     * @throws \Phramework\Exceptions\NotFoundException
     */
    protected static function exists(
        $assert,
        $exceptionMessage = 'Resource not found'
    ) {
        if (!$assert) {
            throw new \Phramework\Exceptions\NotFoundException(
                $exceptionMessage
            );
        }
    }

    /**
     * If !assert then a unknown_error exceptions is thrown.
     *
     * @param mixed  $assert
     * @param string $exceptionMessage [Optional] Default is 'unknown_error'
     * @throws \Exception
     */
    protected static function testUnknownError(
        $assert,
        $exceptionMessage = 'Unknown Error'
    ) {
        if (!$assert) {
            throw new \Exception($exceptionMessage);
        }
    }

    /**
     * View JSONAPI data
     * @param stdClass $data
     * @uses \Phramework\Viewers\JSONAPI
     * @todo use \Phramework\Phramework::view
     */
    public static function viewData(
        $data,
        $links = null,
        $meta = null,
        $included = null
    ) {
        $temp = [];

        if ($links) {
            $temp['links'] = $links;
        }

        $temp['data'] = $data;


        if ($included !== null) {
            $temp['included'] = $included;
        }

        if ($meta) {
            $temp['meta'] = $meta;
        }

        \Phramework\Phramework::view($temp);

        unset($temp);
    }

    /**
     * Extract included related resources from parameters
     * @param  array|object $params Request parameters
     * @return null|array
     */
    protected static function getRequestInclude($params = [])
    {
        //work with arrays
        if (!is_array($params) && is_object($params)) {
            $params = array($params);
        }

        if (!isset($params['include']) || empty($params['include'])) {
            return [];
        }

        $include = [];

        //split parameter using , (for multiple values)
        foreach (explode(',', $params['include']) as $i) {
            $include[] = trim($i);
        }

        return array_unique($include);
    }

    /**
     * Get request data attributes.
     * The request is expected to have json api structure
     * Like the following example:
     * ```
     * [
     *    data => [
     *        'type' => 'user',
     *        'attributes' => [
     *            'email'    => 'nohponex@gmail.com',
     *            'password' => 'XXXXXXXXXXXXXXXXXX'
     *        ]
     *    ]
     * ]
     * ```
     * @param  array|object $params Request parameters
     * @uses Request::requireParameters
     * @return \stdClass
     */
    protected static function getRequestAttributes($params = [])
    {
        //work with arrays
        if (!is_array($params) && is_object($params)) {
            $params = array($params);
        }

        //Require data
        Request::requireParameters($params, ['data']);

        //Require data['attributes']
        Request::requireParameters($params['data'], ['attributes']);

        return (object)$params['data']['attributes'];
    }

    /**
     * Throw a Forbidden exception if resource's id is set.
     *
     * Unsupported request to create a resource with a client-generated ID
     * @package JSONAPI
     * @throws \Phamework\Phramework\Exceptions\ForbiddenException
     * @param  object $resource [description]
     */
    public static function checkIfUnsupportedRequestWithId($resource)
    {
        if (isset($resource->id)) {
            throw new \Phamework\Phramework\Exceptions\ForbiddenException(
                'Unsupported request to create a resource with a client-generated ID'
            );
        }
    }

    /**
     * handles GET requests
     * @param  array  $params  Request parameters
     * @param  string $modelClass                      Resource's primary model
     * to be used
     * @param  array $additionalGetArguments           [Optional] Array with any
     * additional arguments that the primary data is requiring
     * @param  array $additionalRelationshipsArguments [Optional] Array with any
     * additional arguemnt primary data's relationships are requiring
     * @param  boolean $filterable                     [Optional] Deafult is
     * true, if true allowes `filter` URI parameters to be parsed for filtering
     * @param  boolean $filterableJSON                 [Optional] Deafult is
     * false, if true allowes `filter` URI parameters to be parsed for filtering
     * for JSON encoded fields
     * @param  boolean $sortable                       [Optional] Deafult is
     * true, if true allowes sorting
     */
    protected static function handleGET(
        $params,
        $modelClass,
        $additionalGetArguments = [],
        $additionalRelationshipsArguments = [],
        $filterable = true,
        $filterableJSON = false,
        $sortable = true
    ) {
        $page = null;

        $filter = (object)[
            'primary' => null,
            'relationships' => [],
            'attributes' => [],
            'attributesJSON' => []
        ];

        $sort = null;

        if ($filterable && isset($params['filter'])) {
            foreach ($params['filter'] as $filterKey => $filterValue) {
                //todo validate as int

                if ($filterKey === $modelClass::getType()) {
                    //Check filter value type
                    if (!is_string($filterValue) && !is_numeric($filterValue)) {
                        throw new RequestException(sprintf(
                            'String or integer value required for filter "%s"',
                            $filterKey
                        ));
                    }

                    $values = array_map(
                        'intval',
                        array_map('trim', explode(',', trim($filterValue)))
                    );
                    $filter->primary = $values;
                } elseif ($modelClass::relationshipExists($filterKey)) {
                    //Check filter value type
                    if (!is_string($filterValue) && !is_numeric($filterValue)) {
                        throw new RequestException(sprintf(
                            'String or integer value required for filter "%s"',
                            $filterKey
                        ));
                    }

                    $values = array_map(
                        'intval',
                        array_map('trim', explode(',', trim($filterValue)))
                    );

                    $filter->relationships[$filterKey] = $values;

                    //when TYPE_TO_ONE it's easy to filter
                } else {
                    $validationModel = $modelClass::getValidationModel();

                    $filterable = $modelClass::getFilterable();

                    $isJSONFilter = false;

                    //Check if $filterKeyParts and key contains . dot character
                    if ($filterableJSON && strpos($filterKey, '.') !== false) {
                        $filterKeyParts = explode('.', $filterKey);

                        if (count($filterKeyParts) > 2) {
                            throw new RequestException(
                                'Second level filtering for JSON objects is not available'
                            );
                        }

                        $filterSubkey = $filterKeyParts[1];

                        //Hack check $filterSubkey if valid using regexp
                        Validate::regexp(
                            $filterSubkey,
                            '/^[a-zA-Z_\-0-9]{1,30}$/',
                            'filter[' . $filterKey . ']'
                        );

                        $filterKey = $filterKeyParts[0];

                        $isJSONFilter = true;
                    }

                    if (!key_exists($filterKey, $filterable)) {
                        throw new RequestException(sprintf(
                            'Filter key "%s" not allowed',
                            $filterKey
                        ));
                    }

                    $operatorClass = $filterable[$filterKey];

                    if ($isJSONFilter && ($operatorClass & Operator::CLASS_JSONOBJECT) === 0) {
                        throw new RequestException(sprintf(
                            'Filter key "%s" is not accepting JSON object filtering',
                            $filterKey
                        ));
                    }

                    //All must be arrays
                    if (!is_array($filterValue)) {
                        $filterValue = [$filterValue];
                    }

                    foreach ($filterValue as $singleFilterValue) {
                        if (is_array($singleFilterValue)) {
                            throw new RequestException(sprintf(
                                'Array given for filter "%s"',
                                $filterKey
                            ));
                        }

                        $singleFilterValue = urldecode($singleFilterValue);

                        list($operator, $operant) = Operator::parse($singleFilterValue);

                        //Validate operator (check if it's in allowed operators class)
                        if (!in_array(
                            $operator,
                            Operator::getByClassFlags($operatorClass)
                        )) {
                            throw new RequestException(sprintf(
                                'Not allowed operator for field "%s"',
                                $filterKey
                            ));
                        }

                        if ((in_array($operator, Operator::getNullableOperators()))) {
                            //Do nothing for nullable operators
                        } else {
                            if (!$validationModel
                                || !isset($validationModel->properties->{$filterKey})
                            ) {
                                throw new \Exception(sprintf(
                                    'Attribute "%s" doesn\'t have a validation model',
                                    $filterKey
                                ));
                            }

                            if ($isJSONFilter) {
                                //unparsable
                            } else {
                                //Validate operant value
                                $operant = $validationModel->properties
                                    ->{$filterKey}->parse($operant);
                            }
                        }
                        if ($isJSONFilter) {
                            //Push tuple to attribute filters
                            $filter->attributesJSON[] = [$filterKey, $filterSubkey, $operator, $operant];
                        } else {
                            //Push tuple to attribute filters
                            $filter->attributes[] = [$filterKey, $operator, $operant];
                        }
                    }
                }

            }
        }

        //Parse pagination
        if (isset($params['page'])) {
            $tempPage = [];

            if (isset($params['page']['offset'])) {
                $tempPage['offset'] =
                    (new \Phramework\Validate\UnsignedInteger())
                        ->parse($params['page']['offset']);
            }

            if (isset($params['page']['limit'])) {
                $tempPage['limit'] =
                    (new \Phramework\Validate\UnsignedInteger())
                        ->parse($params['page']['limit']);
            }

            if (!empty($tempPage)) {
                $page = (object)$tempPage;
            }
        }

        //Push pagination $page object to end of arguments
        $additionalGetArguments[] = $page;

        if ($filterable) {
            //Push filters to end of arguments
            $additionalGetArguments[] = $filter;
        }

        if ($sortable) {
            $modelSort = $modelClass::getSort();

            $sort = null;

            if ($modelSort->default !== null) {
                $sort = new \stdClass();
                $sort->table = $modelClass::getTable();
                $sort->attribute = $modelSort->default;
                $sort->ascending = $modelSort->ascending;

                //Don't accept arrays
                if (isset($params['sort'])) {
                    if (!is_string($params['sort'])) {
                        throw new RequestException(
                            'String expected for sort'
                        );
                    }

                    $validateExpression =
                        '/^(?P<descending>\-)?(?P<attribute>'
                        . implode('|', $modelSort->attributes)
                        . ')$/';

                    if (!!preg_match($validateExpression, $params['sort'], $matches)) {
                        $sort->attribute = $matches['attribute'];
                        $sort->ascending = (
                            isset($matches['descending']) && $matches['descending']
                            ? false
                            : true
                        );

                    } else {
                        throw new RequestException(
                            'Invalid value for sort'
                        );
                    }
                }
            }

            //Push sort to end of arguments
            $additionalGetArguments[] = $sort;
        }

        $data = call_user_func_array(
            [$modelClass, 'get'],
            $additionalGetArguments
        );

        $requestInclude = static::getRequestInclude($params);

        $includedData = $modelClass::getIncludedData(
            $data,
            $requestInclude,
            $additionalRelationshipsArguments
        );

        static::viewData(
            $data,
            ['self' => $modelClass::getSelfLink()],
            null,
            (empty($requestInclude) ? null : $includedData)
        );
    }

    /**
     * handles GETById requests
     * @param  array  $params                          Request parameters
     * @param  integer|string $id                      Requested resource's id
     * @param  string $modelClass                      Resource's primary model
     * to be used
     * @param  array $additionalGetArguments           [Optional] Array with any
     * additional arguments that the primary data is requiring
     * @param  array $additionalRelationshipsArguments [Optional] Array with any
     * additional arguemnt primary data's relationships are requiring
     */
    protected static function handleGETByid(
        $params,
        $id,
        $modelClass,
        $additionalGetArguments = [],
        $additionalRelationshipsArguments = []
    ) {
        //Rewrite resource's id
        $id = Request::requireId($params);

        $data = call_user_func_array(
            [
                $modelClass,
                $modelClass::GET_BY_PREFIX . ucfirst($modelClass::getIdAttribute())
            ],
            array_merge([$id], $additionalGetArguments)
        );

        //Check if resource exists
        static::exists($data);

        $requestInclude = static::getRequestInclude($params);

        $includedData = $modelClass::getIncludedData(
            $data,
            $requestInclude,
            $additionalRelationshipsArguments
        );

        static::viewData(
            $data,
            ['self' => $modelClass::getSelfLink($id)],
            null,
            (empty($requestInclude) ? null : $includedData)
        );
    }

    /**
     * @todo allow null values
     * @param  array  $params                          Request parameters
     * @param  string $method                          Request method
     * @param  array  $headers                         Request headers
     * @param  integer|string $id                      Requested resource's id
     * @param  string $modelClass                      Resource's primary model
     * to be used
     * @param  array $additionalGetArguments           [Optional] Array with any
     * additional arguments that the primary data is requiring
     */
    protected static function handlePATCH(
        $params,
        $method,
        $headers,
        $id,
        $modelClass,
        $additionalGetArguments = []
    ) {
        $validationModel = new \Phramework\Validate\Object(
            [],
            [],
            false
        );

        $classValidationModel = $modelClass::getValidationModel();

        foreach ($modelClass::getMutable() as $mutable) {
            if (!isset($classValidationModel->properties->{$mutable})) {
                throw new \Exception(sprintf(
                    'Validation model for attribute "%s" is not set!',
                    $mutable
                ));
            }

            $validationModel->addProperty(
                $mutable,
                $classValidationModel->properties->{$mutable}
            );
        }

        $requestAttributes = static::getRequestAttributes($params);

        $attributes = $validationModel->parse($requestAttributes);

        foreach ($attributes as $key => $attribute) {
            if ($attribute === null) {
                unset($attributes->{$key});
            }
        }

        if (count($attributes) === 0) {
            throw new RequestException('No fields updated');
        }

        //Fetch data, to check if resource exists
        $data = call_user_func_array(
            [
                $modelClass,
                $modelClass::GET_BY_PREFIX . ucfirst($modelClass::getIdAttribute())
            ],
            array_merge([$id], $additionalGetArguments)
        );

        //Check if resource exists
        static::exists($data);

        $patch = $modelClass::patch($id, (array)$attributes);

        return static::viewData(
            $modelClass::resource(['id' => $id]),
            ['self' => $modelClass::getSelfLink($id)]
        );
    }

    /**
     * @param  array  $params                          Request parameters
     * @param  string $method                          Request method
     * @param  array  $headers                         Request headers
     * @param  string $modelClass                      Resource's primary model
     */
    protected static function handlePOST(
        $params,
        $method,
        $headers,
        $modelClass
    ) {
        $validationModel = $modelClass::getValidationModel();

        $requestAttributes = static::getRequestAttributes($params);

        $attributes = $validationModel->parse($requestAttributes);

        $id = $modelClass::post((array)$attributes);

        //Prepare response with 201 Created status code
        \Phramework\Models\Response::created(
            $modelClass::getSelfLink($id)
        );

        return static::viewData(
            $modelClass::resource(['id' => $id]),
            ['self' => $modelClass::getSelfLink($id)]
        );
    }

    /**
     * Handle handleByIdRelationships requests
     * @param  array  $params                          Request parameters
     * @param  string $method                          Request method
     * @param  array  $headers                         Request headers
     * @param  integer|string $id                      Resource's id
     * @param  string $relationship                    Requested relationship
     * key
     * @param  string $modelClass                      Resource's primary model
     * to be used
     * @param string[] $allowedMethods                 Allowed methods
     * @param  array $additionalGetArguments           [Optional] Array with any
     * additional arguments that the primary data is requiring
     * @param  array $additionalRelationshipsArguments [Optional] Array with any
     * additional arguments primary data's relationships are requiring
     */
    protected static function handleByIdRelationships(
        $params,
        $method,
        $headers,
        $id,
        $relationship,
        $modelClass,
        $allowedMethods,
        $additionalGetArguments = [],
        $additionalRelationshipsArguments = []
    ) {
        $id = Request::requireId($params);

        $relationship = Filter::string($params['relationship']);

        //Check if relationship exists
        static::exists(
            $modelClass::relationshipExists($relationship),
            'Relationship not found'
        );

        $object = call_user_func_array(
            [
                $modelClass,
                $modelClass::GET_BY_PREFIX . ucfirst($modelClass::getIdAttribute())
            ],
            array_merge([$id], $additionalGetArguments)
        );

        //Check if object exists
        static::exists($object);

        //Check if requested method is allowed
        Validate::enum($method, $allowedMethods);

        //Fetch relationship data
        $data = $modelClass::getRelationshipData(
            $relationship,
            $id,
            $additionalGetArguments,
            (
                isset($additionalRelationshipsArguments[$relationship])
                ? $additionalRelationshipsArguments[$relationship]
                : []
            )
        );

        //Add links
        $links = [
            'self'    =>
                $modelClass::getSelfLink($id) . '/relationships/' . $relationship,
            'related' =>
                $modelClass::getSelfLink($id) . '/' . $relationship
        ];

        static::viewData($data, $links);
    }
}
