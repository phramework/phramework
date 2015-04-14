<?php

require __DIR__ . '/../vendor/autoload.php';

define('APPPATH', __DIR__);
/**
 * Define APP as function
 */
$APP = function() {

    //Include settings
    $settings = require(__DIR__ . '/settings.php');

    $controller_whitelist = [
        'test',
    ];


    //Initialize API
    $API = new Phramework\API\API($settings, $controller_whitelist, ['test'], ['test']);

    unset($settings);

    //Hardcoded (for demo)
    $_GET['controller'] = 'test';
    $_SERVER['REQUEST_METHOD'] = 'HEAD';

    $API->set_viewer('Phramework\API\viewers\print_r');

    //Execute API
    $API->invoke();
};
echo "\n\n\n";
/**
 * Execute APP
 */
$APP();

echo "\n\n\n";
