<?php

require __DIR__ . '/../../../vendor/autoload.php';

define('APPPATH', __DIR__ . '/../');

require APPPATH . '/viewers/viewer.php';

/**
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