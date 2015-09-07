<?php

namespace APP\Controllers;

use Phramework\API;
use Phramework\Models\Validate;
use Phramework\Models\Request;
use APP\Models\Blog as B;

class Blog
{
    public static function GET($params, $method, $headers)
    {
        /*if (($id = Request::resourceId($params)) !== FALSE) {
            echo '<pre>';
            print_r([$params, $method, $headers]);
            echo '</pre>';
            throw new \Phramework\Exceptions\NotImplemented();
        }*/

        $posts = B::getAll();

        API::view([
            'posts' => $posts,
        ], 'blog', 'My blog'); //will load viewers/page/blog.php
    }

    public static function GETSingle($params, $method, $headers)
    {
        $id = Request::requiredId($params);

        $posts = B::getAll();

        array_unshift($posts, []);

        if ($id == 0 || $id > count($posts) - 1) {
            throw new \Phramework\Exceptions\NotFound('Post not found');
        }

        API::view([
            'posts' => [$posts[$id]],
        ], 'blog', 'My blog #' . $id); //will load viewers/page/blog.php
    }
}
