<?php
//Show all errors
error_reporting(E_ALL);
ini_set('display_errors', '1');

//This autoload path is for loading current version of phramework
require __DIR__ . '/../../../vendor/autoload.php';
//This autoload is for loading this APP and any of dependencies
//Only for this example
require __DIR__ . '/../vendor/autoload.php';

//define controller namespace, as shortcut
define('NS', 'APP\\Controllers\\');

use Phramework\API;

/**
 * @package examples/post
 * Define APP as function
 */
$APP = function() {

    //Include settings
    $settings = include __DIR__ . '/../settings.php';

    $URIStrategy = new \Phramework\URIStrategy\URITemplate([
        ['book/', NS . 'BookController', 'GET', API::METHOD_GET],
        ['book/{id}', NS . 'BookController', 'GETSingle', API::METHOD_GET],
        ['book/', NS . 'BookController', 'POST', API::METHOD_ANY]
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
