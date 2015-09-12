<?php
//Show all errors
error_reporting(E_ALL);
ini_set('display_errors', '1');

//This autoload path is for loading current version of phramework
require __DIR__ . '/../../../vendor/autoload.php';
//This autoload is for loading this APP and any of dependencies
require __DIR__ . '/../vendor/autoload.php';

use Phramework\API;

define('APPPATH', __DIR__ . '/../');

/**
 * @package examples/post
 * Define APP as function
 */
$APP = function() {

    //Include settings
    $settings = require(APPPATH . '/settings.php');

    $uriStrategy = new \Phramework\URIStrategy\URITemplate([
        ['book/', 'APP\\Controllers\\BookController', 'GET', API::METHOD_GET],
        ['book/', 'APP\\Controllers\\BookController', 'POST', API::METHOD_POST]
    ]);

    //Initialize API
    $API = new API($settings, $uriStrategy);

    unset($settings);

    $API->setViewerClass('Phramework\Viewers\JSONAPI');

    //Execute API
    $API->invoke();
};
/**
 * Execute APP
 */
$APP();
