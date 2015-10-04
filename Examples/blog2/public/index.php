<?php
//Show all errors
error_reporting(E_ALL);
ini_set('display_errors', '1');

//This autoload path is for loading current version of phramework
require __DIR__ . '/../../../vendor/autoload.php';

use Phramework\API;

/**
 * @package examples/post
 * Define APP as function
 */
$APP = function () {

    //Include settings
    $settings = include __DIR__ . '/../settings.php';

    $controller_whitelist = [
        'post', 'editor'
    ];

    $uriStrategy = new \Phramework\URIStrategy\ClassBased(
        $controller_whitelist,
        ['post', 'editor'],
        ['post', 'editor'],
        "Examples\blog2\APP\\Controllers\\",
        'Controller'
    );

    //Initialize API
    $API = new API($settings, $uriStrategy);

    unset($settings);

    $API->setViewerClass('\Examples\blog2\APP\Viewers\Viewer');

    //Execute API
    $API->invoke();
};
/**
 * Execute APP
 */
$APP();
