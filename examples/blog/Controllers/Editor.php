<?php

namespace APP\Controllers;

use Phramework\API;

class Editor
{
    public static function GET($params)
    {
        API::view([], 'editor', 'Blog\'s Editor');
    }
}
