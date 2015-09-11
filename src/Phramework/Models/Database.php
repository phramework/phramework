<?php

namespace Phramework\Models;

use PDO;

/**
 * Basic mysql postgresql and  model using PDO
 *
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @since 0
 * @package Phramework
 * @category Models
 */
class Database
{
    /**
     * PDO Link object
     * @var object
     */
    protected static $pdoLink = false;
    /**
     * Database driver (mysql, postgresql)
     * @var string
     */
    protected static $dbDriver;
    /**
     * Initialize model, connecto to Database
     *
     * @param string $driver Database driver. Supported : mysql, postgresql
     * @param string $name Database name
     * @param string $username Connection username
     * @param string|NULL $password Connection password
     * @param string $host Connection host, default is localhost
     * @param integer $port Connection port, default is 3306
     * @throws Phramework\Exceptions\Database
     */
    protected function __construct($driver, $name, $username, $password, $host = 'localhost', $port = 3306)
    {
        self::$dbDriver = $driver;

        try {
            if ($driver == 'postgresql') {
                $this->createPostgresqlConnection($name, $username, $password, $host, $port);
            } else {
                $this->createMysqlConnection($name, $username, $password, $host, $port);
            }

            // $this::$pdoLink ->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this::$pdoLink->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\Exception $e) {
            throw new \Phramework\Exceptions\Database('Database Error', $e->getMessage());
        }
    }

    /**
     * Get the db's driver
     * @return string the driver of the db
     **/
    public static function getDbDriver()
    {
        return self::$dbDriver;
    }

    private function createMysqlConnection($name, $username, $password, $host, $port)
    {
        $options = [];
        $options[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = true;

        if (!$this::$pdoLink = new PDO("mysql:dbname={$name};host={$host};port={$port};charset=utf8", $username, $password, $options)) {
            throw new \Phramework\Exceptions\Database('ERROR_DATABASE_CONNECT');
        }
        $this::$pdoLink->query('SET NAMES utf8');
        $this::$pdoLink->query('SET SQL_MODE=ANSI_QUOTES');
    }

    private function createPostgresqlConnection($name, $username, $password, $host, $port)
    {
        if (!$this::$pdoLink = new PDO("pgsql:dbname=$name;host=$host;user=$username;password=$password;port=$port")) {
            throw new \Phramework\Exceptions\Database('ERROR_DATABASE_CONNECT');
        }
    }

    /**
     * Close the Database connection
     */
    public static function close()
    {
        if (Database::$pdoLink) {
            Database::$pdoLink = null;
        }
    }

    /**
     * Connect to Database, if not connected, using credentials stored in global variable
     *
     * @param array $settings associative with name, user, host required. port is optional.
     */
    public static function requireDatabase($settings)
    {
        if (!Database::$pdoLink) {
            $port = $settings['driver'] == 'postgresql' ? 5432 : 3306;

            if (isset($settings['port'])) {
                $port = $settings['port'];
            }

            new Database(
                $settings['driver'],
                $settings['name'],
                $settings['user'],
                $settings['pass'],
                $settings['host'],
                $port
            );

            unset($settings);
        }
    }

    /**
     * Execute a query and return the row count
     *
     * @param string $query
     * @param array $parameters
     * @return type
     * @throws Phramework\Exceptions\Database
     */
    public static function execute($query, $parameters = [])
    {
        try {
            $statement = Database::$pdoLink->prepare($query);
            $statement->execute($parameters);
            return $statement->rowCount();
        } catch (\Exception $e) {
            throw new \Phramework\Exceptions\Database('Database Error', $e->getMessage());
        }
    }

    /**
     * Execute a query and return last instert id
     *
     * @param string $query
     * @param array Query parameters
     * @return type
     * @throws Phramework\Exceptions\Database
     */
    public static function executeLastInsertId($query, $parameters = [])
    {
        try {
            $statement = Database::$pdoLink->prepare($query);
            $statement->execute($parameters);
            return Database::$pdoLink->lastInsertId();
        } catch (\Exception $e) {
            throw new \Phramework\Exceptions\Database('Database Error', $e->getMessage());
        }
    }

    /**
     * Execute a query and fetch first row as associative array
     *
     * @param string $query
     * @param array Query parameters
     * @param array $cast_model [optional] Default is NULL, if set then \Phramework\Models\Filter::castEntry will be applied to data
     * @return type
     * @throws Phramework\Exceptions\Database
     */
    public static function executeAndFetch($query, $parameters = [], $cast_model = null)
    {
        try {
            $statement = Database::$pdoLink->prepare($query);
            $statement->execute($parameters);
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            $statement->closeCursor();
            return (
                $cast_model && $data
                ? \Phramework\Models\Filter::castEntry($data, $cast_model)
                : $data
            );
        } catch (\Exception $e) {
            throw new \Phramework\Exceptions\Database('Database Error', $e->getMessage());
        }
    }

    /**
     * Execute a query and fetch all rows as associative array
     *
     * @param string $query
     * @param array Query parameters
     * @param array $cast_model [optional] Default is NULL, if set then \Phramework\Models\Filter::cast will be applied to data
     * @return type
     * @throws Phramework\Exceptions\Database
     */
    public static function executeAndFetchAll($query, $parameters = [], $cast_model = null)
    {
        try {
            $statement = Database::$pdoLink->prepare($query);
            $statement->execute($parameters);
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
            $statement->closeCursor();
            return (
                $cast_model && $data
                ? \Phramework\Models\Filter::cast($data, $cast_model)
                : $data
            );
        } catch (\Exception $e) {
            throw new \Phramework\Exceptions\Database('Database Error', $e->getMessage());
        }
    }

    /**
     * Execute a query and fetch first row as array
     * @param string $query
     * @param array Query parameters
     * @return type
     * @throws Phramework\Exceptions\Database
     */
    public static function executeAndFetchArray($query, $parameters = [])
    {
        try {
            $statement = Database::$pdoLink->prepare($query);
            $statement->execute($parameters);
            $data = $statement->fetch(PDO::FETCH_COLUMN);
            $statement->closeCursor();
            return $data;
        } catch (\Exception $e) {
            throw new \Phramework\Exceptions\Database('Database Error', $e->getMessage());
        }
    }

    /**
     *
     * @param string $query Query string
     * @param array Query parameters
     * @return type
     * @throws Phramework\Exceptions\Database
     */
    public static function executeAndFetchAllArray($query, $parameters = [])
    {
        try {
            $statement = Database::$pdoLink->prepare($query);
            $statement->execute($parameters);
            $data = $statement->fetchAll(PDO::FETCH_COLUMN);
            $statement->closeCursor();
            return $data;
        } catch (\Exception $e) {
            throw new \Phramework\Exceptions\Database('Database Error', $e->getMessage());
        }
    }

    /**
     * Bind Execute a query and return last instert id
     *
     * @param string $query Query string
     * @param array Query parameters
     * @return type
     * @throws Phramework\Exceptions\Database
     */
    public static function bindExecuteLastInsertId($query, $parameters = [])
    {
        try {
            $statement = Database::$pdoLink->prepare($query);
            foreach ($parameters as $index => $paramProperties) {
                if (is_array($paramProperties)) {
                    $statement->bindValue((int) $index + 1, $paramProperties['value'], $paramProperties['pdo_param']);
                } else {
                    $statement->bindValue((int) $index + 1, $paramProperties);
                }
            }
            $statement->execute();
            return Database::$pdoLink->lastInsertId();
        } catch (\Exception $e) {
            throw new \Phramework\Exceptions\Database('Database Error', $e->getMessage());
        }
    }

    /**
     * Bind Execute a query and return the row count
     *
     * @param string $query Query string
     * @param array Query parameters
     * @return type
     * @throws Phramework\Exceptions\Database
     */
    public static function bindExecute($query, $parameters = [])
    {
        try {
            $statement = Database::$pdoLink->prepare($query);

            foreach ($parameters as $index => $paramProperties) {
                if (is_array($paramProperties)) {
                    $statement->bindValue((int) $index + 1, $paramProperties['value'], $paramProperties['pdo_param']);
                } else {
                    $statement->bindValue((int) $index + 1, $paramProperties);
                }
            }

            $statement->execute();
            return $statement->rowCount();
        } catch (\Exception $e) {
            throw new \Phramework\Exceptions\Database('Database Error', $e->getMessage());
        }
    }

    /**
     * Bind Execute a query and fetch first row as associative array
     *
     * @param string $query Query string
     * @param array Query parameters
     * @param array $cast_model [optional] Default is NULL, if set then \Phramework\Models\Filter::castEntry will be applied to data
     * @return type
     * @throws Phramework\Exceptions\Database
     */
    public static function bindExecuteAndFetch($query, $parameters = [], $cast_model = null)
    {
        try {
            $statement = Database::$pdoLink->prepare($query);
            foreach ($parameters as $index => $paramProperties) {
                if (is_array($paramProperties)) {
                    $statement->bindValue((int) $index + 1, $paramProperties['value'], $paramProperties['pdo_param']);
                } else {
                    $statement->bindValue((int) $index + 1, $paramProperties);
                }
            }
            $statement->execute();
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            $statement->closeCursor();
            return (
                $cast_model && $data
                ? \Phramework\Models\Filter::castEntry($data, $cast_model)
                : $data
            );
        } catch (\Exception $e) {
            throw new \Phramework\Exceptions\Database('Database Error', $e->getMessage());
        }
    }

    /**
     * Bind Execute a query and fetch all rows as associative array
     *
     * @param string $query Query string
     * @param array Query parameters
     * @param array $cast_model [optional] Default is NULL, if set then
     * \Phramework\Models\Filter::castEntry will be applied to data
     * @return type
     * @throws Phramework\Exceptions\Database
     */
    public static function bindExecuteAndFetchAll($query, $parameters = [], $cast_model = null)
    {
        try {
            $statement = Database::$pdoLink->prepare($query);
            foreach ($parameters as $index => $paramProperties) {
                if (is_array($paramProperties)) {
                    $statement->bindValue((int) $index + 1, $paramProperties['value'], $paramProperties['pdo_param']);
                } else {
                    $statement->bindValue((int) $index + 1, $paramProperties);
                }
            }
            $statement->execute();
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);

            return (
                $cast_model && $data
                ? \Phramework\Models\Filter::cast($data, $cast_model)
                : $data
            );
        } catch (\Exception $e) {
            throw new \Phramework\Exceptions\Database('Database Error', $e->getMessage());
        }
    }
}
