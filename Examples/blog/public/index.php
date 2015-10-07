<?php
//Show all errors
error_reporting(E_ALL);
ini_set('display_errors', '1');

//This autoload path is for loading current version of phramework
require __DIR__ . '/../../../vendor/autoload.php';

define('NS', '\\Examples\\blog\\APP\\Controllers\\');

use \Phramework\Phramework;

/**
 * @package examples/post
 * Define APP as function
 */
$APP = function() {

    //Include settings
    $settings = include __DIR__ . '/../settings.php';

    $uriStrategy = new \Phramework\URIStrategy\URITemplate([
        ['/', NS .'PostController', 'GET', Phramework::METHOD_GET],
        ['post/', NS . 'PostController', 'GET', Phramework::METHOD_GET],
        ['post/{id}', NS . 'PostController', 'GETSingle', Phramework::METHOD_GET],
        ['editor',  NS . 'EditorController', 'GET', Phramework::METHOD_GET],
        ['editor', NS . 'EditorController', 'POST', Phramework::METHOD_POST],
        ['secure', NS . 'SecureController', 'GET', Phramework::METHOD_GET, true]
    ]);

    //Initialize API
    $phramework = new Phramework($settings, $uriStrategy);

    unset($settings);

    $phramework->setViewerClass('Examples\blog\APP\Viewers\Viewer');

    //Execute API
    $phramework->invoke();
};

/**
 * Execute APP
 */
$APP();
