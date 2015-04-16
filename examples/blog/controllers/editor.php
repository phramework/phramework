<?php

namespace APP\controllers;

use \Phramework\API\API;

class editor_controller {

    public static function GET($params) {

        API::view([
            'page'  => 'editor', //Will load page blog.php
            'title' => 'Blog\'s Editor',
        ]);
    }

}