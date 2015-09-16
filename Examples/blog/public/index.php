<?php
//Show all errors
error_reporting(E_ALL);
ini_set('display_errors', '1');

//This autoload path is for loading current version of phramework
require __DIR__ . '/../../../vendor/autoload.php';

define('NS', 'Examples\\blog\\APP\\Controllers\\');

use Phramework\API;

/**
 * @package examples/post
 * Define APP as function
 */
$APP = function() {

    //Include settings
    $settings = include __DIR__ . '/../settings.php';

    $uriStrategy = new \Phramework\URIStrategy\URITemplate([
        ['/', NS .'PostController', 'GET', API::METHOD_GET],
        ['post/', NS . 'PostController', 'GET', API::METHOD_GET],
        ['post/{id}', NS . 'PostController', 'GETSingle', API::METHOD_GET],
        ['editor',  NS . 'EditorController', 'GET', API::METHOD_GET],
        ['editor', NS . 'EditorController', 'POST', API::METHOD_POST],
        ['secure', NS . 'SecureController', 'GET', API::METHOD_GET, true]
    ]);

    //Initialize API
    $API = new API($settings, $uriStrategy);

    unset($settings);

    $API->setViewerClass('Examples\blog\APP\Viewers\Viewer');

    //Execute API
    $API->invoke();
};

/**
 * Execute APP
 */
$APP();
