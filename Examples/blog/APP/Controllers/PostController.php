<?php

namespace Examples\blog\APP\Controllers;

use Phramework\API;
use Phramework\Validate\Validate;
use Phramework\Models\Request;
use Examples\blog\APP\Models\Post;

class PostController
{
    public static function GET($params, $method, $headers)
    {
        /*if (($id = Request::resourceId($params)) !== FALSE) {
            echo '<pre>';
            print_r([$params, $method, $headers]);
            echo '</pre>';
            throw new \Phramework\Exceptions\NotImplemented();
        }*/

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
