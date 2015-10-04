<?php

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
