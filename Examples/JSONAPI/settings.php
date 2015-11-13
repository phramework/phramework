<?php

$settings = [
    'debug' => true,
    'language' => 'en',
    'base' => 'http://localhost/Phramework/Examples/JSONAPI/public/',
    'require_db' => true,
    /**
     * Database configuration
     */
    'db' => [
        'driver' => 'mysql',
        'host' => 'db.nohponex.gr',
        'user' => 'phramework',
        'pass' => 'eRxUxyxJvVQrT3LM',
        'name' => 'phramework'
    ],
];

//Overwrite setting if localsettings.php exists and we are not in docker enviroment
if (!file_exists('/.dockerinit') && file_exists(__DIR__ .'/localsettings.php')) {
    include(__DIR__ . '/localsettings.php');
}

return $settings;
