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
namespace Phramework\Tests\Server\Controllers;

use Phramework\Exceptions\Source\Pointer;
use Phramework\Models\Request;
use Phramework\Phramework;
use Phramework\Validate\AnyOf;
use Phramework\Validate\ArrayValidator;
use Phramework\Validate\IntegerValidator;
use Phramework\Validate\ObjectValidator;
use Phramework\Validate\StringValidator;

/**
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 */
class AuthorController
{
    public static function GET($params, $method, $headers) {
        Phramework::view( (object) [
            'data' => 'ok'
        ]);
    }
    public static function POST($params, $method, $headers) {
        Request::requireParameters(
            $params,
            'data',
            new Pointer('')
        );

        $validator = (new ObjectValidator(
            (object) [
                'data' => (new ObjectValidator(
                    (object) [
                        'id' => (new StringValidator(
                            0,
                            64,
                            '/^[1-9][0-9]*$/'
                        )),
                        'type' => (new StringValidator(
                            0,
                            64
                        ))->setEnum(['author']),
                        'attributes' => new ObjectValidator(
                            (object) [
                                'value' => new AnyOf(
                                    new IntegerValidator(),
                                    new ArrayValidator(
                                        1,
                                        2,
                                        new IntegerValidator()
                                    )
                                )
                            ],
                            ['value']
                        )
                    ],
                    ['id', 'type', 'attributes']
                ))
            ],
            ['data']
        ))->setSource(new Pointer(''));

//        var_dump($validator->validate($params));

        $validator->parse($params);

       /* Request::requireParameters(
            $params->data,
            ['id', 'attributes', 'type'],
            new Pointer('/data')
        );*/


        Phramework::view( (object) [
            'data' => 'ok'
        ]);
    }
}