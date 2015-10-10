<?php

//Show all errors
error_reporting(E_ALL);
ini_set('display_errors', '1');

//This autoload path is for loading current version of phramework
require __DIR__ . '/../../../vendor/autoload.php';

//define controller namespace, as shortcut
define('NS', 'Examples\\JSONAPI\\APP\\Controllers\\');

use \Phramework\Phramework;

/**
 * @package examples/post
 * Define APP as function
 */
$APP = function () {

    //Include settings
    $settings = include __DIR__ . '/../settings.php';

    $URIStrategy = new \Phramework\URIStrategy\URITemplate([
        ['test/', NS . 'TestController', 'GET', Phramework::METHOD_GET],
        ['test/', NS . 'TestController', 'POST', Phramework::METHOD_POST],
        ['test/{id}', NS . 'TestController', 'GETById', [Phramework::METHOD_GET, Phramework::METHOD_PATCH]],
        ['test/{id}/relationships/{relationship}', NS . 'TestController', 'byIdRelationships', Phramework::METHOD_ANY],
    ]);

    //Initialize API
    $phramework = new Phramework($settings, $URIStrategy);

    unset($settings);

    $phramework->setViewerClass('\Phramework\Viewers\JSONAPI');

    //Execute API
    $phramework->invoke();
};

/**
 * Execute APP
 */
$APP();
