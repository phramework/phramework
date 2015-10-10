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

/**
 * Base JSONAPI controller
 * @package JSONAPI
 */
class Controller
{
    /**
     * View JSONAPI data
     * @param stdClass $data
     * @uses \Phramework\Viewers\JSONAPI
     * @todo use \Phramework\Phramework::view
     */
    public static function viewData($data, $links = null, $meta = null, $included = null)
    {
        $temp = [

        ];

        if ($links) {
            $temp['links'] = $links;
        }

        $temp['data'] = $data;

        if ($meta) {
            $temp['meta'] = $meta;
        }

        if ($included) {
            $temp['included'] = $included;
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

        //require data
        Request::requireParameters($params, ['data']);

        //require data['attributes']
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
}
