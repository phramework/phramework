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

    $controller_whitelist = [
        'post', 'editor'
    ];

    $uriStrategy = new \Phramework\URIStrategy\ClassBased(
        $controller_whitelist,
        ['post', 'editor'],
        ['post', 'editor'],
        "APP\\Controllers\\",
        'Controller'
    );

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
