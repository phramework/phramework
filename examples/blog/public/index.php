<?php
//Show all errors
error_reporting(E_ALL);
ini_set('display_errors', '1');

//Replace it with your path to vendor
require __DIR__ . '/../../../vendor/autoload.php';
use Phramework\API\API;
define('APPPATH', __DIR__ . '/../');

require APPPATH . '/viewers/viewer.php';

//temporary
require APPPATH . '/controllers/blog.php';
require APPPATH . '/controllers/editor.php';

/**
 * @package examples/blog
 * Define APP as function
 */
$APP = function() {

    //Include settings
    $settings = require(APPPATH . '/settings.php');

    $controller_whitelist = [
        'blog', 'editor'
    ];

    /*$uri_strategy = new \Phramework\API\uri_strategy\classbased(
        $controller_whitelist,
        ['blog', 'editor'],
        ['blog', 'editor'],
        "APP\\controllers\\"
    );*/

    $uri_strategy = new \Phramework\API\uri_strategy\template(
        [
            ['blog', 'APP\\controllers\\blog', 'GET', API::METHOD_GET],
            ['blog/{id}', 'APP\\controllers\\blog', 'GET', API::METHOD_GET],
            ['blog/{id}/author/{order}', 'APP\\controllers\\author', 'GET_by_blog', API::METHOD_GET],
            ['editor', 'APP\\controllers\\editor', 'GET', API::METHOD_GET]
        ]
    );

    //Initialize API
    $API = new API($settings, $uri_strategy);

    unset($settings);

    $API->set_viewer_class('APP\viewers\viewer');

    //Execute API
    $API->invoke();
};
/**
 * Execute APP
 */
$APP();
