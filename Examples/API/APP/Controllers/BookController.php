<?php
namespace Phramework\Examples\API\APP\Controllers;

use \Phramework\Phramework;
use \Phramework\Validate\Validate;
use \Phramework\Models\Request;
use \Phramework\Models\Util;
use \Phramework\Examples\API\APP\Models\Book;

class BookController extends \Phramework\Examples\API\APP\Controller
{
    public static function GET($params, $method, $headers)
    {
        $data = Book::get();

        self::view(['data' => $data]);
    }

    public static function GETSingle($params, $method, $headers, $id)
    {
        $id = Request::requireId($params);

        $data = Book::getById($id);

        self::exists($data);

        self::view([
            'data' => $data
        ]);
    }

    public static function POST($params, $method, $headers)
    {
        throw new \Phramework\Exceptions\NotImplemented();
    }
}
