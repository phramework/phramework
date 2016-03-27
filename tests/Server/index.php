<?php
/**
 * Copyright 2015-2016 Xenofon Spafaridis
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Phramework\Tests\Server;

use Phramework\Phramework;
use Phramework\Route\URITemplate;
use Phramework\Tests\Server\Controllers\AuthorController;

//Show all errors
error_reporting(E_ALL);
ini_set('display_errors', '1');

include __DIR__ . '/../../vendor/autoload.php';

$APP = function () {
    $route = new URITemplate([
        [
            'author/',
            AuthorController::class,
            'GET',
            Phramework::METHOD_GET
        ],
        [
            'author/',
            AuthorController::class,
            'POST',
            Phramework::METHOD_POST
        ],
    ]);

    $phramework = new Phramework(
        [

        ],
        $route
    );

    Phramework::setViewer(
        \Phramework\Viewers\JSON::class
    );
    //Execute API
    $phramework->invoke();
};

$APP();