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
 * Operator's related model
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
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
    const OPERATOR_LIKE = '~~';
    const OPERATOR_NOT_LIKE = '!~~';
    const OPERATOR_IN = 'IN';
    const OPERATOR_NOT_IN = 'NOT IN';

    /**
     * ∈, is an element of array
     */
    const OPERATOR_IN_ARRAY = '∈';
    /**
     * ∉, is not an element of array
     */
    const OPERATOR_NOT_IN_ARRAY = '∉';

    /**
     * @var string[]
     */
    protected static $operators = [
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
        Operator::OPERATOR_NOT_LIKE,
        Operator::OPERATOR_IN_ARRAY,
        Operator::OPERATOR_NOT_IN_ARRAY
    ];

    /**
     * @return string[]
     * @since 1.2.0
     */
    public static function getOperators()
    {
        return self::$operators;
    }

    /**
     * Check if a string is a valid operator
     * @param  string $operator
     * @param  string $attributeName
     *     *[Optional]* Attribute's name, used for thrown exception
     * @throws \Phramework\Exceptions\IncorrectParametersException
     * @return string Returns the operator
     */
    public static function validate($operator, $attributeName = 'operator')
    {
        if (!in_array($operator, self::$operators)) {
            throw new \Phramework\Exceptions\IncorrectParametersException(
                [$attributeName]
            );
        }

        return $operator;
    }

    const CLASS_COMPARABLE = 1;
    const CLASS_ORDERABLE = 2;
    const CLASS_LIKE = 4;
    const CLASS_IN_ARRAY = 32;
    const CLASS_NULLABLE = 64;
    const CLASS_JSONOBJECT = 128;

    /**
     * Get operators
     * @param  integer $classFlags
     * @return integer Operator class
     * @throws \Exception When invalid operator class flags are given
     */
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

        if (($classFlags & Operator::CLASS_LIKE) !== 0) {
            $operators = array_merge(
                $operators,
                Operator::getLikeOperators()
            );
        }

        if (($classFlags & Operator::CLASS_IN_ARRAY) !== 0) {
            $operators = array_merge(
                $operators,
                Operator::getInArrayOperators()
            );
        }

        if (empty($operators)) {
            throw new \Exception('Invalid operator class flags');
        }

        return array_unique($operators);
    }

    /**
     * @param  string $operatorValueString
     * @return string[2] [operator, operand]
     * @example
     * ```php
     * list($operator, $operand) = Operator::parse('>=5');
     * ```
     */
    public static function parse($operatorValueString)
    {
        $operator = Operator::OPERATOR_EQUAL;
        $value = $operatorValueString;

        $operators = implode(
            '|',
            array_merge(
                Operator::getOrderableOperators(),
                Operator::getLikeOperators(),
                Operator::getInArrayOperators()
            )
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

    /**
     * @return string[]
     */
    public static function getNullableOperators()
    {
        return [
            Operator::OPERATOR_ISNULL,
            Operator::OPERATOR_NOT_ISNULL
        ];
    }

    /**
     * @return string[]
     */
    public static function getLikeOperators()
    {
        return [
            Operator::OPERATOR_LIKE,
            Operator::OPERATOR_NOT_LIKE
        ];
    }

    /**
     * @return string[]
     */
    public static function getEqualityOperators()
    {
        return [
            Operator::OPERATOR_EQUAL,
            Operator::OPERATOR_NOT_EQUAL
        ];
    }

    /**
     * @return string[]
     */
    public static function getInArrayOperators()
    {
        return [
            Operator::OPERATOR_IN_ARRAY,
            Operator::OPERATOR_NOT_IN_ARRAY
        ];
    }

    /**
     * @return string[]
     */
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
