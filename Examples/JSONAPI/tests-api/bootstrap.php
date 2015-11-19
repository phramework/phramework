<?php
use \Phramework\Testphase\Testphase;
use \Phramework\Testphase\TestParser;

$settings = include __DIR__ . '/../settings.php';

/**
 * Hack
 * @todo Figure out a more permanent solution
 */
Testphase::setBase($settings['base']);

TestParser::addGlobal(
    'randInteger10',
    rand(1, 10)
);

TestParser::addGlobal(
    'headerRequestContentType',
    'Content-Type: application/vnd.api+json'
);
TestParser::addGlobal(
    'headerRequestAccept',
    'Accept: application/vnd.api+json'
);
TestParser::addGlobal(
    'headerResponseContentType',
    'application/vnd.api+json;charset=utf-8'
);
TestParser::addGlobal(
    'responseBodyJsonapiResource',
    TestParser::getResponseBodyJsonapiResource()
);

TestParser::addGlobal(
    'responseBodyJsonapiCollection',
    TestParser::getResponseBodyJsonapiCollection()
);
