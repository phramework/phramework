<?php
namespace Examples\API\APP\Models;

use \Phramework\Models\Database;

class Book
{
    public static function get()
    {
        return [
            [
               'type' => 'book',
               'id' => 1,
               'attributes' => [
                   'title' => 'A book'
               ]
            ],
            [
                'type' => 'book',
                'id' => 2,
                'attributes' => [
                    'title' => 'Another book'
                ]
            ]
        ];
    }

    public static function getById($id)
    {
        $books = self::get();

        if ($id <= 0 || $id > count($books)) {
            return false;
        }

        return $books[$id-1];
    }
}
