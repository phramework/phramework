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
namespace Phramework\Database;

use \PDO;
use \Phramework\Exceptions\DatabaseException;

/**
 * <br/>Defined settings:<br/>
 * <ul>
 * <li>
 *   array database
 *   <ul>
 *   <li>string  adapter</li>
 *   <li>string  name, Database name</li>
 *   <li>string  username</li>
 *   <li>string  password</li>
 *   <li>string  host</li>
 *   <li>integer port</li>
 *   </ul>
 * </li>
 * </ul>
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @uses \PDO
 */
class MySQL extends \Phramework\Database\PostgreSQL
{
    protected $adapterName = 'mysql';

    public function __construct($settingsDb)
    {
        $options = [];

        //Work with arrays
        if (is_object($settingsDb)) {
            $settingsDb = (array)$settingsDb;
        }

        $options[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = true;
        if (!($this->link = new PDO(
            sprintf(
                'mysql:dbname=%s;host=%s;port=%s;charset=utf8',
                $settingsDb['name'],
                $settingsDb['host'],
                $settingsDb['port']
            ),
            $settingsDb['username'],
            $settingsDb['password'],
            $options
        ))) {
            throw new DatabaseException('Cannot connect to database');
        }

        $this->link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->link->query('SET NAMES utf8');
        $this->link->query('SET SQL_MODE=ANSI_QUOTES');
    }
}
