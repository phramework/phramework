<?php

namespace Phramework\Examples\blog\APP\Controllers;

use Phramework\Phramework;
use Phramework\Validate\Validate;
use Phramework\Models\Request;
use Phramework\Examples\blog\APP\Models\Post;

class EditorController
{
    public static function GET($params)
    {
        Phramework::view([], 'editor', 'Blog\'s Editor');
    }

    public static function POST($params)
    {
        //Define model
        $model = [
            'title' => [
                'type' => Validate::TYPE_TEXT, 'max' => 12,   'min' => 3,  Validate::REQUIRED,
            ],
            'content' => [
                'type' => Validate::TYPE_TEXT, 'max' => 4096, 'min' => 12, Validate::REQUIRED,
            ],
        ];

        //Require and Validate model
        Validate::model($params, $model);

        //Declare them as variables
        $title = $params['title'];
        $content = $params['content'];

        $post = ['title' => $title, 'content' => $content, 'timestamp' => time()];

        $post = \Phramework\Models\Filter::castEntry(
            $post,
            ['timestamp' => Validate::TYPE_UNIX_TIMESTAMP]
        );

        //Store ($title, $content) somehow and get the id

        $id = rand(0, 100);

        $post['id'] = $id;

        \Phramework\Models\Response::created('http://localhost/post/'. $id .'/');

        //Sample output
        Phramework::view([
            'post' => $post,
        ], 'post', 'Blog post');
    }
}
