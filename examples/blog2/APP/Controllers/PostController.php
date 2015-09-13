<?php

namespace APP\Controllers;

use Phramework\API;
use Phramework\Models\Validate;
use Phramework\Models\Request;
use APP\Models\Post;

class PostController
{
    public static function GET($params, $method, $headers)
    {
        if (($id = Request::resourceId($params)) !== FALSE) {
            return self::GETSingle($params, $method, $headers);
        }

        $posts = Post::getAll();

        API::view([
            'posts' => $posts,
        ], 'blog', 'My blog'); //will load viewers/page/blog.php
    }

    public static function GETSingle($params, $method, $headers)
    {
        $id = Request::requireId($params);

        $posts = Post::getAll();

        array_unshift($posts, []);

        if ($id == 0 || $id > count($posts) - 1) {
            throw new \Phramework\Exceptions\NotFound('Post not found');
        }

        API::view([
            'posts' => [$posts[$id]],
        ], 'blog', 'My blog #' . $id); //will load viewers/page/blog.php
    }
}
