<?php

namespace APP\controllers;

use Phramework\API\API;
use Phramework\API\models\validate;
use Phramework\API\models\request;
use APP\models\blog;

class blog_controller {

    public static function GET($params) {
        include(APPPATH. '/models/blog.php');

        if (($id = request::resource_id($params)) !== FALSE) {
            throw new \Phramework\API\exceptions\not_implemented();
        }

        $posts = blog::get_all();

        API::view([
            'posts' => $posts
        ], 'blog', 'My blog'); //will load viewers/page/blog.php
    }

    public static function POST($params) {
        //Define model
        $model = [
            'title'     => [
                'type' => validate::TYPE_TEXT, 'max' => 12,   'min' => 3,  validate::REQUIRED
            ],
            'content'   => [
                'type' => validate::TYPE_TEXT, 'max' => 4096, 'min' => 12, validate::REQUIRED
            ]
        ];

        //Require and validate model
        validate::model($params, $model);

        //Declare them as variables
        $title      = $params['title'];
        $content    = $params['content'];

        $post = ['title' => $title, 'content' => $content, 'timestamp' => time()];

        $post = \Phramework\API\models\filter::cast_entry(
            $post,
            ['timestamp' => validate::TYPE_UNIX_TIMESTAMP]
        );

        //Store ($title, $content) somehow and get the id

        $id = rand(0, 100);

        $post['id'] = $id;

        \Phramework\API\models\response::created('http://localhost/post/' . $id . '/');

        //Sample output
        API::view([
            'post' => $post
        ], 'post', 'Blog post');
    }
}
