<?php
namespace APP\Controllers;

use Phramework\API;
use Phramework\Models\Validate;
use Phramework\Models\Request;
use APP\Models\Book;

class BookController
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
            'attributes' => $account,
            'links' => [
                'self' => Util::url('user', $id),
            ],
            'relationships' => [
                'contents' => [
                    'links' => [
                        'self' => Util::url('user/'.$id.'/relationships/content/'),
                        'related' => Util::url('user/'.$id.'/content/'),
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
