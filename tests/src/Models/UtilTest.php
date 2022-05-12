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
namespace Phramework\Models;

/**
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 */
class UtilTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Phramework\Models\Util::generateUUID
     */
    public function testGenerateUUID()
    {
        $UUID = Util::generateUUID();

        $this->assertIsString($UUID);

        $this->assertSame(36, strlen($UUID), 'Must be 36 characters');

        $this->assertNotEquals(
            $UUID,
            Util::generateUUID(),
            'Two requests must return a different UUID'
        );
    }

    /**
     * @covers Phramework\Models\Util::token
     */
    public function testToken()
    {
        $token = Util::token();

        $this->assertIsString($token);

        $this->assertSame(40, strlen($token), 'Must be 40 characters');

        $this->assertNotEquals(
            $token,
            Util::token(),
            'Two requests must return a different token'
        );
    }

    /**
     * @covers Phramework\Models\Util::readableRandomString
     */
    public function testReadableRandomString()
    {
        $readableRandomString = Util::readableRandomString(10);

        $this->assertIsString($readableRandomString);

        $this->assertSame(
            10,
            strlen($readableRandomString),
            'Must be 10 characters'
        );

        $this->assertNotEquals(
            $readableRandomString,
            Util:: readableRandomString(),
            'Two requests must return a different token'
        );
    }

    /**
     * @covers Phramework\Models\Util::extension
     * @dataProvider extensionProvider
     */
    public function testExtension($filePath, $expectedExtension)
    {
        $this->assertSame($expectedExtension, Util::extension($filePath));
    }

    /**
     * @return array[]
     */
    public function extensionProvider()
    {
        //$filePath, $expectedExtension
        return [
            ['file.txt', 'txt'],
            ['file.txt.gz', 'gz'],
            ['/var/www/file.txt.gz', 'gz'],
            ['./file.ogg', 'ogg'],
            ['../../file.ogg', 'ogg'],
            ['C:\\Windows\\file.txt.gz', 'gz']
        ];
    }
}
