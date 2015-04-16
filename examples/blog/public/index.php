<?php
//Show all errors
error_reporting(E_ALL);
ini_set('display_errors', '1');

//Replace it with your path to vendor
require __DIR__ . '/../../../vendor/autoload.php';

define('APPPATH', __DIR__ . '/../');

require APPPATH . '/viewers/viewer.php';

/**
 * @package examples/blog
 * Define APP as function
 */
$APP = function() {

    //Include settings
    $settings = require(APPPATH . '/settings.php');

    $controller_whitelist = [
        'blog', 'editor'
    ];


    //Initialize API
    $API = new Phramework\API\API($settings, $controller_whitelist,
        ['blog', 'editor'], ['blog', 'editor']);

    unset($settings);

    $API->set_viewer('APP\viewers\viewer');

    //Execute API
    $API->invoke();
};
/**
 * Execute APP
 */
$APP();