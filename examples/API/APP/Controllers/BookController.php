<?php
namespace APP\Controllers;

use Phramework\API;
use Phramework\Models\Validate;
use Phramework\Models\Request;
use Phramework\Models\Util;
use APP\Models\Book;

class BookController extends \APP\Controller
{
    public static function GET($params)
    {


        self::view(['$params' => $params]);
    }

    public static function GETSingle($params, $method)
    {
        $id = Request::requireId($params);

        $data = [
            'type' => 'book',
            'id' => (string) $id,
            'attributes' => [
                'title' => 'Ena vivlio'
            ],
            'links' => [
                'self' => Util::url('book', $id),
            ],
            'relationships' => [
                'authror' => [
                    'links' => [
                        'self' => Util::url('book/'.$id.'/relationships/authror/'),
                        'related' => Util::url('book/'.$id.'/authror/'),
                    ],
                ],
            ]
        ];

        self::view([
            'data' => $data,
            'meta' => [
                '$params' => $params,
                '$method' => $method
            ]
        ]);
    }
    public static function POST($params, $method, $headers)
    {
        API::view([
            'params' => $params,
            'method' => $method
        ]);
    }
}
