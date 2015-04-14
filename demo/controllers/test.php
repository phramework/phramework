<?php

namespace APP\controllers;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of test
 *
 * @author Sammy Guergachi <sguergachi at gmail.com>
 */
class test_controller {

    public static function GET($params) {
        \Phramework\API\API::view(['test' => 'test1']);
    }

}
