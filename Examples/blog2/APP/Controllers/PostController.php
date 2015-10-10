<?php

namespace Examples\blog2\APP\Controllers;

use Phramework\Phramework;
use Phramework\Validate\Validate;
use Phramework\Models\Request;
use Examples\blog2\APP\Models\Post;

class PostController
{
    public static function GET($params, $method, $headers)
    {
        if (($id = Request::resourceId($params)) !== FALSE) {
            return self::GETSingle($params, $method, $headers);
        }

        $posts = Post::getAll();

        Phramework::view([
            'posts' => $posts,
        ], 'blog', 'My blog'); //will load viewers/page/blog.php
    }

    public static function GETSingle($params, $method, $headers)
    {
        $id = Request::requireId($params);

        $posts = Post::getAll();

        array_unshift($posts, []);

        if ($id == 0 || $id > count($posts) - 1) {
            throw new \Phramework\Exceptions\NotFoundException('Post not found');
        }

        Phramework::view([
            'posts' => [$posts[$id]],
        ], 'blog', 'My blog #' . $id); //will load viewers/page/blog.php
    }
}
