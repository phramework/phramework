<?php

$settings = [
    'debug' => true,
    'language' => 'en',
    'base' => 'http://localhost/Phramework/Examples/JSONAPI/public/',
    /**
     * Database configuration
     */
    'db' => [
        'driver' => 'mysql',
        'host' => '',
        'user' => '',
        'pass' => '',
        'name' => '',
        'port' => 3306
    ],
];

//Overwrite setting if localsettings.php exists and we are not in docker enviroment
if (!file_exists('/.dockerinit') && file_exists(__DIR__ .'/localsettings.php')) {
    include(__DIR__ . '/localsettings.php');
}

return $settings;
