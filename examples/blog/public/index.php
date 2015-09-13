<?php
//Show all errors
error_reporting(E_ALL);
ini_set('display_errors', '1');

//This autoload path is for loading current version of phramework
require __DIR__ . '/../../../vendor/autoload.php';
//This autoload is for loading this APP and any of dependencies
//Only for this example
require __DIR__ . '/../vendor/autoload.php';

use Phramework\API;

/**
 * @package examples/post
 * Define APP as function
 */
$APP = function() {

    //Include settings
    $settings = include __DIR__ . '/../settings.php';

    $uriStrategy = new \Phramework\URIStrategy\URITemplate([
        ['/', 'APP\\Controllers\\PostController', 'GET', API::METHOD_GET],
        ['post/', 'APP\\Controllers\\PostController', 'GET', API::METHOD_GET],
        ['post/{id}', 'APP\\Controllers\\PostController', 'GETSingle', API::METHOD_GET],
        ['editor', 'APP\\Controllers\\EditorController', 'GET', API::METHOD_GET],
        ['editor', 'APP\\Controllers\\EditorController', 'POST', API::METHOD_POST],
        ['secure', 'APP\\Controllers\\SecureController', 'GET', API::METHOD_GET, true]
    ]);

    //Initialize API
    $API = new API($settings, $uriStrategy);

    unset($settings);

    $API->setViewerClass('APP\Viewers\Viewer');

    //Execute API
    $API->invoke();
};

/**
 * Execute APP
 */
$APP();
