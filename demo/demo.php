<?php

require __DIR__ . '/../vendor/autoload.php';

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

    //Execute API
    $API->invoke();
};

/**
 * Execute APP
 */
$APP();
