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
     * @todo use \Phramework\API::view
     */
    public static function viewData($data, $links = null, $meta = null)
    {
        $temp = [
            'data' => $data
        ];

        if ($links) {
            $temp['links'] = $links;
        }

        if ($meta) {
            $temp['meta'] = $meta;
        }

        (new \Phramework\Viewers\JSONAPI())->view($temp);

        unset($temp);
    }

    /**
     * Throw a Forbidden exception if resource's id is set.
     *
     * Unsupported request to create a resource with a client-generated ID
     * @package JSONAPI
     * @throws \Phamework\Exceptions\Forbidden
     * @param  [type] $resource [description]
     * @return [type]           [description]
     */
    public static function checkIfUnsupportedRequestWithId($resource)
    {
        if (isset($resource->id)) {
            throw new \Phamework\Exceptions\Forbidden(
                'Unsupported request to create a resource with a client-generated ID'
            );
        }
    }
}
