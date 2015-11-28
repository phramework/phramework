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
namespace Phramework\Database\Operations;

use \Phramework\Validate\Validate;
use \Phramework\Database\Database;
use \Phramework\Exceptions\RequestExceptionException;
use \Phramework\Exceptions\NotFoundException;

/**
 * create model
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @since 0


 */
class Create
{
    //const RETURN_RECORDS = 1;
    const RETURN_ID = 0;
    const RETURN_NUMBER_OF_RECORDS = 2;

    /**
     * Create a new record in database
     * @param  array $attributes  Key-value array with records's attributes
     * @param  string $table       Table's name
     * @param  string|null $schema     [Optional] Table's schema, default is null for no schema
     * @param  [type] $return      Return method type
     * @return int|array
     * @todo Check RETURNING id for another primary key attribute
     */
    public static function create(
        $attributes,
        $table,
        $schema = null,
        $return = self::RETURN_ID
    ) {
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
        } else {
            //Return number of records affected
            if ($driver == 'postgresql') {
                $query .= 'RETURNING ' . '*';
            }

            return Database::executeAndFetch($query, $query_values);
        }
    }
}
