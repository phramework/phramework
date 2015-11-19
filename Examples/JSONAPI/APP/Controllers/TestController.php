<?php

namespace Examples\JSONAPI\APP\Controllers;

use \Phramework\Phramework;
use \Phramework\Validate\Validate;
use \Phramework\Models\Filter;
use \Phramework\Models\Request;
use \Examples\JSONAPI\APP\Models\Test;

use \Phramework\Validate\UnsignedInteger;
use \Phramework\Validate\Enum;
use \Phramework\Validate\Integer;
use \Phramework\Validate\Number;
use \Phramework\Validate\Object;
use \Phramework\Validate\Boolean;
use \Phramework\Validate\String;
use \Phramework\Validate\ArrayValidator;

/**
 * Controller for /test endpoint
 */
class TestController extends \Examples\JSONAPI\APP\Controller
{
    /**
     * Get collection
     * @param  array  $params  Request parameters
     * @param  string $method  Request method
     * @param  array  $headers  Request headers
     */
    public static function GET($params, $method, $headers)
    {
        return self::handleGET(
            $params,
            Test::class,
            [],
            [],
            true
        );
    }

    /**
     * Get a resource by `id`
     * @param  array  $params  Request parameters
     * @param  string $method  Request method
     * @param  array $headers  Request headers
     * @throws \Phramework\Exceptions\NotFoundException If resource doesn't exist or is
     * inaccessible
     */
    public static function GETById($params, $method, $headers, $id)
    {
        $id = Validate::uint($id);

        return self::handleGETById(
            $params,
            $id,
            Test::class,
            [],
            []
        );
    }

    /**
     * Create a new resource
     * @param  array  $params  Request parameters
     * @param  string $method  Request method
     * @param  array $headers  Request headers
     * @see http://jsonapi.org/format/#crud-creating
     *
     * @example Request example
     * ```
     * POST test/
     * Content-Type: application/vnd.api+json
     *
     * {
     *   "data": {
     *     "type": "test",
     *     "attributes": {
     *       "title": "my title"
     *     }
     *   }
     * }
     * ```
     * @throws \Phramework\Exceptions\MissingParametersException\Forbidden If id is set
     */
    public static function POST($params, $method, $headers)
    {
        return self::handlePOST(
            $params,
            $method,
            $headers,
            Test::class
        );
    }

    /**
     * Update a resource by `id`
     * @param  array  $params  Request parameters
     * @param  string $method  Request method
     * @param  array $headers  Request headers
     * @throws \Phramework\Exceptions\NotFoundException If resource doesn't exist or is
     * inaccessible
     */
    public static function PATCH($params, $method, $headers, $id)
    {
        $id = Request::requireId($params);

        return self::handlePATCH(
            $params,
            $method,
            $headers,
            $id,
            Test::class
        );
    }

    /**
     * Delete a resource by `id`
     * @param  array  $params  Request parameters
     * @param  string $method  Request method
     * @param  array $headers  Request headers
     * @throws \Phramework\Exceptions\NotFoundException If resource doesn't exist or is
     * inaccessible
     * @todo
     */
    public static function DELETE($params, $method, $headers)
    {
        $id = Request::requireId($params);

        throw new \Phramework\Exceptions\NotImplementedException();
    }

    /**
     * Manage resource's relationships
     * `/test/{id}/relationships/{relationship}` handler
     * @param  array  $params  Request parameters
     * @param  string $method  Request method
     * @param  array $headers  Request headers
     */
    public static function byIdRelationships($params, $method, $headers, $id, $relationship)
    {
        parent::handleByIdRelationships(
            $params,
            $method,
            $headers,
            $id,
            $relationship,
            Test::class,
            [\Phramework\Phramework::METHOD_GET],
            [],
            []
        );
    }
}
