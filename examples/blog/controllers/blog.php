<?php

namespace APP\controllers;

use Phramework\API\API;
use Phramework\API\models\validate;
use APP\models\blog;
class blog_controller {

    public static function GET($params) {
        include(APPPATH. '/models/blog.php');

        $posts = blog::get_all();

        API::view([
            'posts' => $posts
        ], 'blog', 'My blog'); //will load viewers/page/blog.php
    }

    public static function POST($params) {
        //Define model
        $model = [
            'title'     => [
                'type' => validate::TYPE_TEXT, 'max' => 12,   'min' => 3,  'required'
            ],
            'content'   => [
                'type' => validate::TYPE_TEXT, 'max' => 4096, 'min' => 12, 'required'
            ]
        ];

        //Require and validate model
        validate::model($params, $model);

        //Declare them as variables
        $title      = $params['title'];
        $content    = $params['content'];

        //Store ($title, $content) somehow

        //Sample output
        API::view([
            'error' => [$title, $content]
        ], 'blog post');
    }

}
