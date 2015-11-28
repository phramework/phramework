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

use \Phramework\Database\Database;
use \Phramework\Validate\Validate;

// @codingStandardsIgnoreStart
/**
 * Provides varius helper functions for searching
 * @todo Change OPERATOR types from validate
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @since 0
 * @deprecated
 */
class Search
{
    /**
     * Perform a full search
     *
     * @param type $parameters
     * @param type $model
     * @param type $fields
     * @param type $order
     * @param type $page
     * @param type $pages
     * @return type
     * @throws RequestException
     */
    public static function search($parameters, $model, &$fields, $order = false, $page = 1, &$pages = 0)
    {
        //Search model (returned to caller )
        $fields = [];

        foreach ($model['fields'] as $key => $value) {
            if (in_array('searchable', $value)) {
                if (isset($parameters[$key . '_operator'])) {
                    if (empty($parameters[$key . '_operator'])) {
                        continue;
                    }
                    $operator = Validate::operator($parameters[$key . '_operator']);
                    $v        = (isset($parameters[$key . '_value']) ? html_entity_decode(Util::userContent($parameters[$key . '_value'])) : '');
                    $fields[$key ] = ['operator' => $operator, 'value' =>  $v ];
                }
            }
        }
        if (!$fields) {
            throw new \Exception('No search fields set');
        }
        $table = $model['table'];
        $index = $model['index'];

        $order_string = ($order ? '"' . $order['field'] . '" ' . ($order['asc'] ? 'ASC' : 'DESC') : ' "' . $table . '"."' . $index . '" DESC');

        $query = 'SELECT "' . $table . '".* FROM "' . $table . '" WHERE ';

        //Query binding parameters
        $p = [];
        //Initialize query array fields
        $fields_query = [];
        foreach ($fields as $key => $v) {
            $operator = $v['operator'];
            $value    = $v['value'];

            switch ($operator) {
                case OPERATOR_EQUAL:
                case OPERATOR_NOT_EQUAL:
                case OPERATOR_GREATER:
                case OPERATOR_GREATER_EQUAL:
                case OPERATOR_LESS:
                case OPERATOR_LESS_EQUAL:
                    if (empty($value)) {
                        break;
                    }
                    //@TODO FIX
                    //$fields_query[] = "$key" $operator ? ";
                    $p[] = $value;
                    break;
                case OPERATOR_IN:
                case OPERATOR_NOT_IN:
                    if (empty($value)) {
                        break;
                    }
                    //Split multiplevalues
                    $values = explode(',', $value);
                   /* $tmp = array_fill(0, count($values), '?' );
                    $tmp = implode(',', $tmp );*/
                    $tmp = trim(str_repeat('?,', count($values)), ',');
                    //@TODO FIX
                    //$fields_query[]  = " "$key" $operator($tmp )";
                    foreach ($values as $tmp) {
                        $p[] = trim($tmp);
                    }
                    break;
                case OPERATOR_ISNULL:
                case OPERATOR_NOT_ISNULL:
                    //@TODO FIX
                    //$fields_query[] = " $operator("$key" ) ";
                    break;
                case OPERATOR_LIKE:
                case OPERATOR_NOT_LIKE:
                    if (empty($value)) {
                        break;
                    }
                    //TODO FIX
                    //$fields_query[]  = " "$key" $operator ? ";
                    $p[] = '%' . $value . '%';
                    break;
            }
        }
        if (!$fields_query) {
            throw new \Exception('No search fields set');
        }
        if (isset($model['listing_fields'])) {
            $select_fields = '"' . implode('","', $model['listing_fields']) . '"';
            //$select_fields = rtrim($select_fields, ',"' );
        } else {
            //@TODO FIX
            //$select_fields = " "$table".*";
        }
        //Set limit query string
        $limit = (($page - 1) * ITEMS_PER_PAGE) . ',' . ITEMS_PER_PAGE;

        //TODO OR OR AND ??
        $data = Database::executeAndFetchAll("SELECT $select_fields" . 'FROM "' . $table . '" WHERE "'
            . implode('OR', $fields_query) . ' ORDER BY ' . $order_string . " LIMIT $limit", $p);

        if ($page == 1 && count($data) < ITEMS_PER_PAGE) {
            $pages = 1;
        } else {
            $pages = Database::executeAndFetch('SELECT COUNT("' . $table . '"."' . $index . '" ) AS count FROM "' .
                $table . '" WHERE ' . implode('OR', $fields_query) . ' ORDER BY ' . $order_string, $p);
            $pages = ceil(floatval($pages['count']) / ITEMS_PER_PAGE);
        }

        return $data;
    }
}

// @codingStandardsIgnoreEnd
