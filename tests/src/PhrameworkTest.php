<?php
/**
 * Copyright 2015 Xenofon Spafaridis
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
namespace Phramework;

/**
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 */
class PhrameworkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Phramework
     */
    protected $phramework;

    /**
     * This method is called before a test is executed.
     */
    public function setUp():void
    {
        //Prepare phramework instance
        $this->phramework = new Phramework(
            [],
            new \Phramework\URIStrategy\URITemplate([
            ])
        );
    }

    /**
     * @covers Phramework\Phramework::getRequestUUID
     */
    public function testGetRequestUUID()
    {
        $requestUUID = Phramework::getRequestUUID();

        $this->assertIsString($requestUUID);

        $this->assertSame(36, strlen($requestUUID), 'Must be 36 characters');

        $this->assertSame(
            $requestUUID,
            Phramework::getRequestUUID(),
            'Two requests must return same UUID'
        );
    }
}
