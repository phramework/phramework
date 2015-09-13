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

    public static function GETSingle($params)
    {
        $id = Request::requireId($params);

        $data = [
            'type' => 'user',
            'id' => (string) $id,
            'attributes' => [],
            'links' => [
                'self' => Util::url('user', $id),
            ],
            'relationships' => [
                'authror' => [
                    'links' => [
                        'self' => Util::url('user/'.$id.'/relationships/authror/'),
                        'related' => Util::url('user/'.$id.'/authror/'),
                    ],
                ],
            ],
        ];

        self::view(['data' => $data, '$params' => '$params']);
    }
    public static function POST($params, $method, $headers)
    {
        API::view([
            'params' => $params,
            'method' => $method
        ]);
    }
}
