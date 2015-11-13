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
        //NOTES:
        //useful params (request parameter):
        //- include http://jsonapi.org/format/#fetching-includes
        //- sort http://jsonapi.org/format/#fetching-sorting
        //- fields[TYPE] http://jsonapi.org/format/#fetching-sparse-fieldsets
        //- filter reserved
        //- page pagination

        $data = Test::get();

        $include = self::getRequestInclude($params);

        $includedData = Test::getIncludedData($data, $include);

        self::viewData(
            $data,
            ['self' => Test::getSelfLink()],
            null,
            $includedData
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
    public static function GETById($params, $method, $headers, $id, $coll)
    {
        $id = Validate::uint($id);

        $data = Test::getById($id);

        //Check if resource exists
        self::exists($data);

        $include = self::getRequestInclude($params);

        $includedData = Test::getIncludedData($data, $include);

        self::viewData(
            $data,
            ['self' => Test::getSelfLink($id)],
            null,
            $includedData
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
            $headers
            Test::class,
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
    public static function PATCH($params, $method, $headers, $id = null)
    {
        $id = Request::requireId($params);

        return self::handlePATCH(
            $params,
            $method,
            $headers,
            $id,
            Test::class,
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
    public static function byIdRelationships($params, $method, $headers, $id = null, $relationship = null)
    {
        $id = Validate::uint($params['id']);

        $relationship = Filter::string($params['relationship']);

        //Check if relationship exists
        self::exists(Test::relationshipExists($relationship), 'Relationship not found');

        //Check if method is allowed
        $allowedMethods = [Phramework::METHOD_GET];
        Validate::enum($method, $allowedMethods);

        //see http://localhost:8080/v1/authors/2/relationships/books
        $data = Test::getRelationshipData($relationship, $id);

        $meta = [
            'relationships' => true,
            'id' => $id,
            'relationship' => $relationship
        ];

        $links = [
            'self'     =>
                Test::getSelfLink($id) . '/relationships/' . $relationship,
            'related'  =>
                Test::getSelfLink($id) . '/' . $relationship
        ];

        self::viewData($data, $links, $meta);
    }

    /*public static function GETByIdRelationship($params, $method, $headers)
    {
        //$id
        //relationship
        //see http://localhost:8080/v1/authors/2/books
        //this will return all book collection with author_id 2
        $id = Validate::uint($params['id']);
        $relationship = Filter::string($params['relationship']);
        (new \Phramework\Viewers\JSONAPI())->view([
            'relationships' => false,
            'id' => $id,
            'relationship' => $relationship
        ]);
    }*/
}
