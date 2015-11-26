<?php
/**
 * Copyright 2015 Spafaridis Xenofon
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

use \Phramework\Exceptions\DatabaseException;
use \PDO;

/**
 * Database model
 * Defined settings:
 * - db[]
 *   - adapter, Adapter's name (lowercase)
 *   - name, Database name
 *   - username
 *   - password
 *   - host
 *   - port
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @since 0
 * @todo Add option to convert fetched data into objects insted of array
 */
class Database
{
    /**
     * @var IAdapter
     */
    protected static $adapter;

    public static function setAdapter($adapter)
    {
        if (!($adapter instanceof \Phramework\Database\IAdapter)) {
            throw new \Exception(
                'Class is not implementing \Phramework\Database\IAdapter'
            );
        }

        self::$adapter = $adapter;
    }

    /**
     * Get adapter's name
     * @return string Adapter's name (lowercase)
     */
    public function getAdapterName()
    {
        return self::$adapter->getAdapterName();
    }

    /**
     * Execute a query and return the row count
     *
     * @param string $query
     * @param array $parameters
     * @return integer Returns the number of rows affected or selected
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public static function execute($query, $parameters = [])
    {
        try {
            return self::$adapter->execute($query, $parameters);
        } catch (\Exception $e) {
            throw new DatabaseException('Database Error', $e->getMessage());
        }
    }

    /**
     * Execute a query and return last instert id
     *
     * @param string $query
     * @param array  $parameters Query parameters
     * @return mixed Returns returns the id of last inserted item
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public static function executeLastInsertId($query, $parameters = [])
    {
        try {
            return self::$adapter->executeLastInsertId($query, $parameters);
        } catch (\Exception $e) {
            throw new DatabaseException('Database Error', $e->getMessage());
        }
    }

    /**
     * Execute a query and fetch first row as associative array
     *
     * @param string $query
     * @param array  $parameters Query parameters
     * @param array $castModel [Optional] Default is null, if set then
     * \Phramework\Models\Filter::castEntry will be applied to data
     * @return array
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public static function executeAndFetch($query, $parameters = [], $castModel = null)
    {
        try {
            return self::$adapter->executeAndFetch($query, $parameters);
        } catch (\Exception $e) {
            throw new DatabaseException('Database Error', $e->getMessage());
        }
    }

    /**
     * Execute a query and fetch all rows as associative array
     *
     * @param string $query
     * @param array  $parameters Query parameters
     * @param array $castModel [Optional] Default is null, if set then
     * \Phramework\Models\Filter::cast will be applied to data
     * @return array[]
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public static function executeAndFetchAll($query, $parameters = [], $castModel = null)
    {
        try {
            return self::$adapter->executeAndFetchAll($query, $parameters);
        } catch (\Exception $e) {
            throw new DatabaseException('Database Error', $e->getMessage());
        }
    }

    /**
     * Execute a query and fetch first row as array
     * @param string $query
     * @param array  $parameters Query parameters
     * @return array
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public static function executeAndFetchArray($query, $parameters = [])
    {
        try {
            return self::$adapter->executeAndArray($query, $parameters);
        } catch (\Exception $e) {
            throw new DatabaseException('Database Error', $e->getMessage());
        }
    }

    /**
     *
     * @param string $query Query string
     * @param array  $parameters Query parameters
     * @return array[]
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public static function executeAndFetchAllArray($query, $parameters = [])
    {
        try {
            return self::$adapter->executeAndFetchAllArray($query, $parameters);
        } catch (\Exception $e) {
            throw new DatabaseException('Database Error', $e->getMessage());
        }
    }

    /**
     * Bind Execute a query and return last instert id
     *
     * @param string $query Query string
     * @param array  $parameters Query parameters
     * @return mixed
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public static function bindExecuteLastInsertId($query, $parameters = [])
    {
        try {
            return self::$adapter->bindExecuteLastInsertId($query, $parameters);
        } catch (\Exception $e) {
            throw new DatabaseException('Database Error', $e->getMessage());
        }
    }

    /**
     * Bind Execute a query and return the row count
     *
     * @param string $query Query string
     * @param array  $parameters Query parameters
     * @return integer Returns the number of rows affected or selected
     * @throws \Phramework\Exceptions\DatabaseException
     * @todo provide documentation
     */
    public static function bindExecute($query, $parameters = [])
    {
        try {
            return self::$adapter->bindExecute($query, $parameters);
        } catch (\Exception $e) {
            throw new DatabaseException('Database Error', $e->getMessage());
        }
    }

    /**
     * Bind Execute a query and fetch first row as associative array
     *
     * @param string $query Query string
     * @param array  $parameters Query parameters
     * @param array  $castModel [Optional] Default is null, if set
     * then \Phramework\Models\Filter::castEntry will be applied to data
     * @return type
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public static function bindExecuteAndFetch($query, $parameters = [], $castModel = null)
    {
        try {
            return self::$adapter->bindExecuteAndFetch($query, $parameters);
        } catch (\Exception $e) {
            throw new DatabaseException('Database Error', $e->getMessage());
        }
    }

    /**
     * Bind Execute a query and fetch all rows as associative array
     *
     * @param string $query Query string
     * @param array  $parameters Query parameters
     * @param array  $castModel [Optional] Default is null, if set then
     * \Phramework\Models\Filter::castEntry will be applied to data
     * @return type
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public static function bindExecuteAndFetchAll($query, $parameters = [], $castModel = null)
    {
        try {
            return self::$adapter->bindExecuteAndFetchAll($query, $parameters);
        } catch (\Exception $e) {
            throw new DatabaseException('Database Error', $e->getMessage());
        }
    }

    /**
     * Close the Database connection
     */
    public static function close()
    {
        try {
            if (self::$adapter) {
                self::$adapter->close();
            }
        } catch (\Exception $e) {
        }
    }
}
