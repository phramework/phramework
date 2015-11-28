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

use \PDO;
use \Phramework\Exceptions\DatabaseException;

/**
 * Defined settings:
 * - db[]
 *   - adapter, Adapter's name (lowercase)
 *   - name, Database name
 *   - username
 *   - password
 *   - host
 *   - port
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @uses \PDO
 */
class PostgreSQL implements \Phramework\Database\IAdapter
{
    /**
     * @var PDO
     */
    protected $link;

    protected $adapterName = 'postgresql';

    /**
     * Get adapter's name
     * @return string Adapter's name (lowercase)
     */
    public function getAdapterName()
    {
        return $this->adapterName;
    }

    public function __construct($settingsDb)
    {
        if (!($this->link = new PDO(sprintf(
            "pgsql:dbname=%s;host=%s;user=%s;password=%s;port=%s",
            $settingsDb['name'],
            $settingsDb['host'],
            $settingsDb['username'],
            $settingsDb['password'],
            $settingsDb['port']
        )))) {
            throw new DatabaseException('Cannot connect to database');
        }

        $this->link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Execute a query and return the row count
     *
     * @param string $query
     * @param array $parameters
     * @return integer Returns the number of rows affected or selected
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public function execute($query, $parameters = [])
    {
        $statement = $this->link->prepare($query);
        $statement->execute($parameters);
        return $statement->rowCount();
    }

    /**
     * Execute a query and return last instert id
     *
     * @param string $query
     * @param array  $parameters Query parameters
     * @return mixed Returns the id of last inserted item
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public function executeLastInsertId($query, $parameters = [])
    {
        $statement = $this->link->prepare($query);
        $statement->execute($parameters);
        return $this->link->lastInsertId();
    }


    /**
     * Execute a query and fetch first row as associative array
     *
     * @param string $query
     * @param array  $parameters Query parameters
     * @param array $castModel [Optional] Default is null, if set then
     * \Phramework\Models\Filter::castEntry will be applied to data
     * @return array Returns a single row from database
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public function executeAndFetch($query, $parameters = [], $castModel = null)
    {
        $statement = $this->link->prepare($query);
        $statement->execute($parameters);
        $data = $statement->fetch(PDO::FETCH_ASSOC);
        $statement->closeCursor();

        return (
            $castModel && $data
            ? \Phramework\Models\Filter::castEntry($data, $castModel)
            : $data
        );
    }

    /**
     * Execute a query and fetch all rows as associative array
     *
     * @param string $query
     * @param array  $parameters Query parameters
     * @param array $castModel [Optional] Default is null, if set then
     * \Phramework\Models\Filter::cast will be applied to data
     * @return array[] Returns multiple rows from database
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public function executeAndFetchAll($query, $parameters = [], $castModel = null)
    {
        $statement = $this->link->prepare($query);
        $statement->execute($parameters);
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        $statement->closeCursor();
        return (
            $castModel && $data
            ? \Phramework\Models\Filter::cast($data, $castModel)
            : $data
        );
    }


    /**
     * Execute a query and fetch first row as array
     * @param string $query
     * @param array  $parameters Query parameters
     * @return array
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public function executeAndFetchArray($query, $parameters = [])
    {
        $statement = $this->link->prepare($query);
        $statement->execute($parameters);
        $data = $statement->fetch(PDO::FETCH_COLUMN);
        $statement->closeCursor();
        return $data;
    }

    /**
     *
     * @param string $query Query string
     * @param array  $parameters Query parameters
     * @return array[]
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public function executeAndFetchAllArray($query, $parameters = [])
    {
        $statement = $this->link->prepare($query);
        $statement->execute($parameters);
        $data = $statement->fetchAll(PDO::FETCH_COLUMN);
        $statement->closeCursor();
        return $data;
    }

    /**
     * Bind Execute a query and return last instert id
     *
     * @param string $query Query string
     * @param array  $parameters Query parameters
     * Example [123, ['value' => 123, 'params' => \PDO::PARAM_INT]]
     * @return mixed
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public function bindExecuteLastInsertId($query, $parameters = [])
    {
        $statement = $this->link->prepare($query);
        foreach ($parameters as $index => $paramProperties) {
            if (is_array($paramProperties)) {
                $statement->bindValue(
                    (int) $index + 1,
                    $paramProperties['value'],
                    $paramProperties['param']
                );
            } else {
                $statement->bindValue((int) $index + 1, $paramProperties);
            }
        }
        $statement->execute();
        return $this->link->lastInsertId();
    }

    /**
     * Bind Execute a query and return the row count
     *
     * @param string $query Query string
     * @param array  $parameters Query parameters
     * Example [123, ['value' => 123, 'params' => \PDO::PARAM_INT]]
     * @return integer
     * @throws \Phramework\Exceptions\DatabaseException
     * @todo provide documentation
     */
    public function bindExecute($query, $parameters = [])
    {
        $statement = $this->link->prepare($query);

        foreach ($parameters as $index => $paramProperties) {
            if (is_array($paramProperties)) {
                $statement->bindValue(
                    (int) $index + 1,
                    $paramProperties['value'],
                    $paramProperties['param']
                );
            } else {
                $statement->bindValue((int) $index + 1, $paramProperties);
            }
        }

        $statement->execute();
        return $statement->rowCount();
    }

    /**
     * Bind Execute a query and fetch first row as associative array
     *
     * @param string $query Query string
     * @param array  $parameters Query parameters
     * Example [123, ['value' => 123, 'params' => \PDO::PARAM_INT]]
     * @param array $castModel [Optional] Default is null, if set
     * then \Phramework\Models\Filter::castEntry will be applied to data
     * @return array
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public function bindExecuteAndFetch($query, $parameters = [], $castModel = null)
    {
        $statement = $this->link->prepare($query);
        foreach ($parameters as $index => $paramProperties) {
            if (is_array($paramProperties)) {
                $statement->bindValue(
                    (int) $index + 1,
                    $paramProperties['value'],
                    $paramProperties['param']
                );
            } else {
                $statement->bindValue((int) $index + 1, $paramProperties);
            }
        }
        $statement->execute();
        $data = $statement->fetch(PDO::FETCH_ASSOC);
        $statement->closeCursor();
        return (
            $castModel && $data
            ? \Phramework\Models\Filter::castEntry($data, $castModel)
            : $data
        );
    }

    /**
     * Bind Execute a query and fetch all rows as associative array
     *
     * @param string $query Query string
     * @param array  $parameters Query parameters
     * Example [123, ['value' => 123, 'params' => \PDO::PARAM_INT]]
     * @param array $castModel [Optional] Default is null, if set then
     * \Phramework\Models\Filter::castEntry will be applied to data
     * @return array[]
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public function bindExecuteAndFetchAll($query, $parameters = [], $castModel = null)
    {
        $statement = $this->link->prepare($query);
        foreach ($parameters as $index => $paramProperties) {
            if (is_array($paramProperties)) {
                $statement->bindValue(
                    (int) $index + 1,
                    $paramProperties['value'],
                    $paramProperties['param']
                );
            } else {
                $statement->bindValue((int) $index + 1, $paramProperties);
            }
        }
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        return (
            $castModel && $data
            ? \Phramework\Models\Filter::cast($data, $castModel)
            : $data
        );
    }

    /**
     * Close the connection to database
     */
    public function close()
    {
        $this->link = null;
    }
}
