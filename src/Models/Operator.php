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

/**
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @package Phramework
 * @category Models
 * @since 1.0.0
 */
class Operator
{

    const OPERATOR_ISSET = 'isset';
    const OPERATOR_NOT_ISSET = '!isset';
    const OPERATOR_GREATER = '>';
    const OPERATOR_GREATER_EQUAL = '>=';
    const OPERATOR_LESS = '<';
    const OPERATOR_LESS_EQUAL = '<=';
    const OPERATOR_EQUAL = '=';
    const OPERATOR_NOT_EQUAL = '!=';
    const OPERATOR_ISNULL = 'ISNULL';
    const OPERATOR_NOT_ISNULL = '!ISNULL';
    const OPERATOR_EMPTY = 'empty';
    const OPERATOR_NOT_EMPTY = '!empty';
    const OPERATOR_LIKE = 'LIKE';
    const OPERATOR_NOT_LIKE = 'NOT LIKE';
    const OPERATOR_IN = 'IN';
    const OPERATOR_NOT_IN = 'NOT IN';

    public static $operators = [
        Operator::OPERATOR_EMPTY,
        Operator::OPERATOR_EQUAL,
        Operator::OPERATOR_GREATER,
        Operator::OPERATOR_GREATER_EQUAL,
        Operator::OPERATOR_ISSET,
        Operator::OPERATOR_LESS,
        Operator::OPERATOR_LESS_EQUAL,
        Operator::OPERATOR_NOT_EMPTY,
        Operator::OPERATOR_NOT_EQUAL,
        Operator::OPERATOR_NOT_ISSET,
        Operator::OPERATOR_ISNULL,
        Operator::OPERATOR_NOT_ISNULL,
        Operator::OPERATOR_IN,
        Operator::OPERATOR_NOT_IN,
        Operator::OPERATOR_LIKE,
        Operator::OPERATOR_NOT_LIKE
    ];

    public static function validate($operator)
    {
        if (!in_array($operator, self::$operators)) {
            throw new \Phramework\Exceptions\IncorrectParametersException(
                ['operator']
            );
        }

        return $operator;
    }

    const CLASS_COMPARABLE = 1;
    const CLASS_ORDERABLE = 2;
    const CLASS_NULLABLE = 64;

    public static function getByClassFlags($classFlags)
    {
        $operators = [];

        if (($classFlags & Operator::CLASS_COMPARABLE) !== 0) {
            $operators = array_merge(
                $operators,
                Operator::getEqualityOperators()
            );
        }

        if (($classFlags & Operator::CLASS_ORDERABLE) !== 0) {
            $operators = array_merge(
                $operators,
                Operator::getOrderableOperators()
            );
        }

        if (($classFlags & Operator::CLASS_NULLABLE) !== 0) {
            $operators = array_merge(
                $operators,
                Operator::getNullableOperators()
            );
        }

        if (empty($operators)) {
            throw new \Exception('Invalid operator class flags');
        }

        return array_unique($operators);
    }

    public static function parse($operatorValueString)
    {
        $operator = Operator::OPERATOR_EQUAL;
        $value = $operatorValueString;

        $operators = implode(
            '|',
            Operator::getOrderableOperators()
        );

        if (!!preg_match(
            '/^(' . $operators . ')[\ ]{0,1}(.+)$/',
            $operatorValueString,
            $matches
        )) {
            return [$matches[1], $matches[2]];
        } elseif (!!preg_match(
            '/^(' . implode('|', Operator::getNullableOperators()) . ')$/',
            $operatorValueString,
            $matches
        )) {
            return [$matches[1], null];
        }

        return [$operator, $value];
    }

    public static function getNullableOperators()
    {
        return [
            Operator::OPERATOR_ISNULL,
            Operator::OPERATOR_NOT_ISNULL
        ];
    }

    public static function getEqualityOperators()
    {
        return [
            Operator::OPERATOR_EQUAL,
            Operator::OPERATOR_NOT_EQUAL
        ];
    }

    public static function getOrderableOperators()
    {
        return [
            Operator::OPERATOR_EQUAL,
            Operator::OPERATOR_NOT_EQUAL,
            Operator::OPERATOR_GREATER_EQUAL,
            Operator::OPERATOR_GREATER,
            Operator::OPERATOR_LESS_EQUAL,
            Operator::OPERATOR_LESS
        ];
    }
}
