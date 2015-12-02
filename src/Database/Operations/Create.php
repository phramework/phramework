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
namespace Phramework\Database\Operations;

use \Phramework\Validate\Validate;
use \Phramework\Database\Database;
use \Phramework\Exceptions\RequestExceptionException;
use \Phramework\Exceptions\NotFoundException;

/**
 * Create operation for databases
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @since 0
 */
class Create
{

    const RETURN_ID = 1;
    const RETURN_RECORDS = 2;
    const RETURN_NUMBER_OF_RECORDS = 4;

    /**
     * Create a new record in database
     * @param  array|object $attributes  Key-value array or object with records's attributes
     * @param  string $table       Table's name
     * @param  string|null $schema [Optional] Table's schema, default is null for no schema
     * @param  integer $return     Return method type
     * - if RETURN_ID will return the id of last inserted record
     * - if RETURN_RECORDS will return the inserted record
     * - if RETURN_NUMBER_OF_RECORDS will return the number of records affected
     * @return integer|array
     * @todo Check RETURNING id for another primary key attribute
     */
    public static function create(
        $attributes,
        $table,
        $schema = null,
        $return = self::RETURN_ID
    ) {
        if (is_object($attributes)) {
            $attributes = (array)$attributes;
        }

        //prepare query
        $query_keys   = implode('" , "', array_keys($attributes));
        $query_parameter_string = trim(str_repeat('?,', count($attributes)), ',');
        $query_values = array_values($attributes);

        $query = 'INSERT INTO ';

        if ($schema !== null) {
            $query .= sprintf('"%s"."%s"', $schema, $table);
        } else {
            $query .= sprintf('"%s"', $table);
        }

        $query .= sprintf(
            ' ("%s") VALUES (%s)',
            $query_keys,
            $query_parameter_string
        );

        $driver = Database::getAdapterName();

        if ($return == self::RETURN_ID) {
            //Return inserted id
            if ($driver == 'postgresql') {
                $query .= ' RETURNING id';

                $id = Database::executeAndFetch($query, $query_values);
                return $id['id'];
            }

            return Database::executeLastInsertId($query, $query_values);
        } elseif ($return == self::RETURN_RECORDS) {
            //Return records
            if ($driver != 'postgresql') {
                throw new \Phramework\Excetpions\ServerExcetion(
                    'RETURN_RECORDS works only with postgresql adapter'
                );
            }

            $query .= 'RETURNING *';
            return Database::executeAndFetch($query, $query_values);
        } else {
            //Return number of records affected
            return Database::execute($query, $query_values);
        }
    }
}
