<?php
//Show all errors
error_reporting(E_ALL);
ini_set('display_errors', '1');

//This autoload path is for loading current version of phramework
require __DIR__ . '/../../../vendor/autoload.php';

use \Phramework\Phramework;

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
        '\\Phramework\\Examples\blog2\APP\\Controllers\\',
        'Controller'
    );

    //Initialize API
    $phramework = new Phramework($settings, $uriStrategy);

    unset($settings);

    Phramework::setViewer(
        \Phramework\Examples\blog2\APP\Viewers\Viewer::Class
    );

    //Execute API
    $phramework->invoke();
};

/**
 * Execute APP
 */
$APP();
