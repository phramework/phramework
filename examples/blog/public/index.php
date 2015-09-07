<?php

//Show all errors
error_reporting(E_ALL);
ini_set('display_errors', '1');

//Replace it with your path to vendor
require __DIR__ . '/../../../vendor/autoload.php';

use Phramework\API;

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

    /*$uri_strategy = new \Phramework\URIStrategy\ClassBased(
        $controller_whitelist,
        ['blog', 'editor'],
        ['blog', 'editor'],
        "APP\\controllers\\"
    );*/

    $uri_strategy = new \Phramework\URIStrategy\URITemplate(
        [
            ['/', 'APP\\controllers\\blog', 'GET', API::METHOD_GET],
            ['blog/', 'APP\\controllers\\blog', 'GET', API::METHOD_GET],
            ['blog/{id}', 'APP\\controllers\\blog', 'GETSingle', API::METHOD_GET],
            ['editor', 'APP\\controllers\\editor', 'GET', API::METHOD_GET],
            ['editor', 'APP\\controllers\\editor', 'POST', API::METHOD_POST]
        ]
    );

    //Initialize API
    $API = new API($settings, $uri_strategy);

    unset($settings);

    $API->setViewerClass('APP\viewers\viewer');

    //Execute API
    $API->invoke();
};
/**
 * Execute APP
 */
$APP();
