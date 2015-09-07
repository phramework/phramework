<?php

namespace APP\controllers;

use \Phramework\API\API;

class editor {

    public static function GET($params) {

        API::view([], 'editor', 'Blog\'s Editor');
    }
}
