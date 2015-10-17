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
namespace Phramework\Models;

use \Phramework\Models\Database;

/**
 * Provides various methods for filtering data
 *
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @since 0
 * @package Phramework
 * @category Models
 */
class Query
{
    protected $table;
    protected $where = [];
    protected $limit = null;
    protected $offset = null;
    protected $join = [];

    private function __construct($table)
    {
        $this->table = $table;
    }

    public static function table($table)
    {
        $query = new Query($table);

        return $query;
    }

    public function where($leftField, $operator, $rightField)
    {
        $this->where[] = [$leftField, $operator, $rightField, false];

        return $this;
    }

    public function whereLiteral($leftField, $operator, $rightField)
    {
        $this->where[] = [$leftField, $operator, $rightField, true];

        return $this;
    }

    public function limit($limit, $offset = null)
    {
        $this->limit = $limit;
        $this->offset = $offset;

        return $this;
    }

    public function join($table, $leftField, $operator, $rightField)
    {
        $this->join[] = [$table, $leftField, $operator, $rightField, false];

        return $this;
    }

    public function joinLiteral($table, $leftField, $operator, $rightField)
    {
        $this->join[] = [$table, $leftField, $operator, $rightField, true];

        return $this;
    }

    private function escape($input)
    {
        if ($input === '*') {
            return $input;
        }

        return sprintf('"%s"', $input);
    }

    public function getSingle($fields = ['*'])
    {
        return $this->limit(1)->get($fields);
    }

    public function get($fields = ['*'])
    {
        if (!is_array($fields)) {
            $fields = [$fields];
        }

        foreach ($fields as &$field) {
            $field = implode(
                '.',
                array_map([$this, 'escape'], explode('.', $field))
            );
        }

        $query = sprintf(
            'SELECT %s'
            . "\n" . 'FROM "%s"',
            implode(', ', $fields),
            $this->table
        );

        foreach ($this->join as list($table, $leftField, $operator, $rightField, $rightLiteral)) {
            $leftField = implode(
                '.',
                array_map([$this, 'escape'], explode('.', $leftField))
            );

            if ($rightLiteral) {
                $rightField = sprintf("'%s'", $rightField);
            } else {
                $rightField = implode(
                    '.',
                    array_map([$this, 'escape'], explode('.', $rightField))
                );
            }

            $query .= sprintf(
                "\n" . 'JOIN "%s"'
                . "\n" . '  ON %s %s %s',
                $table,
                $leftField,
                $operator,
                $rightField
            );
        }

        $whereIndex = 0;
        foreach ($this->where as list($leftField, $operator, $rightField, $rightLiteral)) {

            //$leftField = implode('".', );
            $leftField = implode(
                '.',
                array_map([$this, 'escape'], explode('.', $leftField))
            );

            if ($rightLiteral) {
                $rightField = sprintf("'%s'", $rightField);
            } else {
                $rightField = implode(
                    '.',
                    array_map([$this, 'escape'], explode('.', $rightField))
                );
            }

            $query .= sprintf(
                "\n" . '%s %s %s %s',
                ($whereIndex === 0 ? 'WHERE' : '  AND'),
                $leftField,
                $operator,
                $rightField
            );
            ++$whereIndex;
        }

        if ($this->limit) {
            $query .= ($this->offset === null
                ? sprintf(
                    "\n" . 'LIMIT %s',
                    $this->limit
                )
                : sprintf(
                    "\n" . 'LIMIT %s OFFSET %s',
                    $this->limit,
                    $this->offset
                )
            );
        }

        echo PHP_EOL;
        echo($query);
        echo PHP_EOL;

        return $query;
    }
}
