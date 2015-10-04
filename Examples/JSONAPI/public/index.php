<?php

//Show all errors
error_reporting(E_ALL);
ini_set('display_errors', '1');

//This autoload path is for loading current version of phramework
require __DIR__ . '/../../../vendor/autoload.php';

//define controller namespace, as shortcut
define('NS', 'Examples\\JSONAPI\\APP\\Controllers\\');

use \Phramework\API;

/**
 * @package examples/post
 * Define APP as function
 */
$APP = function () {

    //Include settings
    $settings = include __DIR__ . '/../settings.php';

    $URIStrategy = new \Phramework\URIStrategy\URITemplate([
        ['test/', NS . 'TestController', 'GET', API::METHOD_GET],
        ['test/', NS . 'TestController', 'POST', API::METHOD_POST],
        ['test/{id}', NS . 'TestController', 'GETById', API::METHOD_GET],
        ['test/{id}/relationships/{relationship}', NS . 'TestController', 'byIdRelationships', API::METHOD_ANY],
    ]);

    //Initialize API
    $API = new API($settings, $URIStrategy);

    unset($settings);

    $API->setViewerClass('Phramework\Viewers\JSONAPI');

    //Execute API
    $API->invoke();
};

/**
 * Execute APP
 */
$APP();
