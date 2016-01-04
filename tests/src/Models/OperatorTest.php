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
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 */
class OperatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Phramework\Models\Operator::parse
     * @dataProvider parseProvider
     */
    public function testParse($input, $expected)
    {
        list($expectedOperator, $expectedOperant) = $expected;
        list($operator, $operant) = Operator::parse($input);

        $this->assertEquals(
            $operator,
            $expectedOperator
        );

        $this->assertEquals(
            $operant,
            $expectedOperant
        );
    }

    /**
     * @covers Phramework\Models\Operator::getByClassFlags
     */
    public function testGetByClassFlags()
    {
        $expected = array_unique(array_merge(
            Operator::getEqualityOperators(),
            Operator::getNullableOperators()
        ));

        $operators = Operator::getByClassFlags(
            Operator::CLASS_COMPARABLE | Operator::CLASS_NULLABLE
        );

        $this->assertSame(
            $expected,
            $operators
        );
    }

    /**
     * @covers Phramework\Models\Operator::getByClassFlags
     * @expectedException Exception
     */
    public function testGetByClassFlagsFailure()
    {
        Operator::getByClassFlags(0);
    }

    /**
     * @return array[]
     */
    public function parseProvider()
    {
        //prepare provider values for all operators
        $provider = [];

        foreach (Operator::getNullableOperators() as $operator) {
            $provider[] = [
                $operator,
                [$operator, null]
            ];
        }

        $operators = array_merge(
            Operator::getEqualityOperators(),
            Operator::getOrderableOperators(),
            Operator::getLikeOperators()
        );

        foreach ($operators as $operator) {
            $value = (string)rand(0, 100);

            $provider[] = [
                sprintf(
                    '%s%s',
                    $operator,
                    $value
                ),
                [$operator, $value]
            ];
        }

        return $provider;
    }
}
