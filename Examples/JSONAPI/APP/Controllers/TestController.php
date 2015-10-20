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
    public static function GETById($params, $method, $headers)
    {
        $id = Validate::uint($params['id']);

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
     *       "title": "testxxxxx"
     *     }
     *   }
     * }
     * ```
     * @throws \Phramework\Exceptions\MissingParametersException\Forbidden If id is set
     */
    public static function POST($params, $method, $headers)
    {
        /*var_dump(
            (new Number(0, 1))->parse(3)
        );*/
        $validator = new Object(
            [
                'some_int' => new Integer(),
                'my_string' => new String(0,1),
                'some_object' => (new Object(
                    [
                        'another_int' => new Integer(),
                        'my_string' => new String(0,1)
                    ],
                    ['int']
                ))
            ],
            ['some_int', 'some_object']
        );
        
        $validator = new Object(
            [
                'arr' => new ArrayValidator(
                    1,
                    5,
                    new Integer(1,100),
                    null,
                    true
                )
            ],
            ['arr']
        );
        
        var_dump($validator->parse([
            'arr' => [
                1, 2, '13'
            ]
        ]));
        
        die();
        
        var_dump(
            $validator->parse([
                'some_int' => 1.10,
                'my_string' => 'dasdasds',
                'some_object' => [
                    
                ]
            ])
        );
        die();
        var_dump(
            (new Number(0, 1))->validate("azure")
        );
        die();

        //Extract resource object from request parameters
        $resource = (object)$params['data'];

        Test::validate($resource->attributes);

        //Throw Forbidden exception if id is set
        self::checkIfUnsupportedRequestWithId($resource);

        //Assert resource type
        validate::enum($resource->type, [Test::getType()]);

        //Append created_user_id @todo add real value
        $resource->attributes['created_user_id'] = 1;

        $input = ['weight' => 5, 'length' => 1.02];



        $validatorRequest = new Object(
            [
                'url' => new String(1, 1024),
                'method' => (new Enum(Phramework::$methodWhitelist, true))
                    ->setDefault(Phramework::METHOD_GET),
                //'headers' => new ArrayValidator()
            ],
            ['url']
        );
        $validatorResponse = new Object(
            [
                'statusCode' => new UnsignedInteger(100,999)
            ],
            ['statusCode']
        );

        //Setup validator for parsed test
        $validator = new Object(
            [
                'order' => (new Integer(-99999999,-99999999))
                    ->setDefault(0),
                'request' => $validatorRequest,
                'response' => $validatorResponse
            ],
            ['request', 'response']
        );

        //var_dump($validationObject);
        //var_dump($validationObject->validate($input));

        //Create a new record using request resource's attributes and return id
        $id = Test::post($resource->attributes);

        if (!$id) {
            self::testUnknownError($id);
        }

        //Server MUST return a 201 Created status code
        \Phramework\Models\Response::created(Test::getSelfLink($id));

        $data = Test::getById($id);

        //The response MUST also include a document that contains the primary resource created.
        self::viewData(
            $data,
            ['self' => Test::getSelfLink($id)]
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
    public static function PATCH($params, $method, $headers)
    {

    }

    /**
     * Delete a resource by `id`
     * @param  array  $params  Request parameters
     * @param  string $method  Request method
     * @param  array $headers  Request headers
     * @throws \Phramework\Exceptions\NotFoundException If resource doesn't exist or is
     * inaccessible
     */
    public static function DELETE($params, $method, $headers)
    {

    }

    /**
     * Manage resource's relationships
     * `/test/{id}/relationships/{relationship}` handler
     * @param  array  $params  Request parameters
     * @param  string $method  Request method
     * @param  array $headers  Request headers
     */
    public static function byIdRelationships($params, $method, $headers)
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
