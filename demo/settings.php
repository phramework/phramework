<?php

/**
 * API Server Settings file
 */
$settings = [
    'languages' => ['en-GB', 'el-GR'],
    'language' => 'en-GB',
    'debug' => TRUE,
    'maintenance' => FALSE,
    'allowed_referer' => [ 'localhost', '127.0.0.1'],
    'api_base' => 'http://localhost/metaphrase/api/',
    'executable' => '/',
    'errorlog_path' => '../error_log.txt',
    'email_accounts' => [
        'default' => ['address' => 'phramework@nohponex.gr', 'name' => 'phramework']
    ],
    'db' => [
        'driver' => 'mysql',
        'host' => 'db.nohponex.gr',
        'user' => 'phramework',
        'pass' => 'f7ZG4sxAWQRLnDrr',
        'name' => 'phramework'
    ],
    'salt' => 'abcdef'
];

/**
 * Include a localsettings file if exists
 */
$localsettings = @include 'localsettings.php';
if (is_array($localsettings)) {
    foreach ($localsettings as $key => $value) {
        $settings[$key] = $value;
    }
}
return $settings;
