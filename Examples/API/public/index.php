<?php

//Show all errors
error_reporting(E_ALL);
ini_set('display_errors', '1');

//This autoload path is for loading current version of phramework
require __DIR__ . '/../../../vendor/autoload.php';

//define controller namespace, as shortcut
define('NS', '\\Phramework\\Examples\\API\\APP\\Controllers\\');

use \Phramework\Phramework;

/**
 * @package examples/post
 * Define APP as function
 */
$APP = function () {

    //Include settings
    $settings = include __DIR__ . '/../settings.php';

    $URIStrategy = new \Phramework\URIStrategy\URITemplate([
        ['book/', NS . 'BookController', 'GET', Phramework::METHOD_GET],
        ['book/{id}', NS . 'BookController', 'GETSingle', Phramework::METHOD_GET],
        ['book/', NS . 'BookController', 'POST', Phramework::METHOD_ANY]
    ]);

    //Initialize API
    $phramework = new Phramework($settings, $URIStrategy);

    unset($settings);

    Phramework::setViewer(
        \Phramework\Viewers\JSON::class
    );

    //Execute API
    $phramework->invoke();
};

/**
 * Execute APP
 */
$APP();
