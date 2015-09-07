<?php

namespace APP\models;

class blog
{
    public static function get_all()
    {
        return [
            ['title' => 'post1', 'content' => str_repeat('Lorem ipsum... ', 15)],
            ['title' => 'post2', 'content' => str_repeat('More Lorem ipsum... ', 15)],
            ['title' => 'post3', 'content' => str_repeat('Even More Lorem ipsum... ', 7)],
        ];
    }
}
