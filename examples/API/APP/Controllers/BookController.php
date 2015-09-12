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
        API::view(['params' => $params]);
    }

    public static function POST($params, $method, $headers)
    {
        API::view(['params' => $params]);
    }
}
