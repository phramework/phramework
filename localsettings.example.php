<?php
/**
 * This is our localsettings.php file
 * Copy and rename localsettings.example.php to localsettings.php
 * 
 * This file is used in a non docker enviroment.
 * These settings will override the base settings specified in settings.php.
 * You can override any additional key defined in settings.php.
 * This example contains the most frequently used keys.
 */

$settings['debug']                           = TRUE;
$settings['maintenance']                     = FALSE;
$settings['api_base']                        = 'http://localhost:4444/api/';
$settings['interface_base']                  = 'http://localhost:4444/web/';
$settings['translate']['fetch_keys']         = FALSE;
$settings['translate']['track_missing_keys'] = FALSE;
$settings['email'] = [
    'default' => [ 'mail' => 'mail@localhost', 'name' => 'localhost']
];
/**
 * Sample Database configuration
 * 
    $settings['db'] = [
        'host' => '127.0.0.1',
        'user' => 'yyyyyy',
        'pass' => 'xxxxxx',
        'name' => 'zzzzzz',
        'port' => 3306
    ];
 * 
 * 
 */
