<?php

namespace Phramework\Validate;

class ArrayValidatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ArrayValidator
     */
    protected $object;

    /**
     * Sets up the fixture
     */
    protected function setUp()
    {
        $this->object = new ArrayValidator(1, 3);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    public function validateSuccessProvider()
    {
        //input
        return [
            [[2, '3']],
            [['2', '3']],
            [[1, 2, 3]]
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            ['0 items' => []],
            ['>3 items' => [1,2,3,4,5,6]]
        ];
    }

    /**
     * @dataProvider validateSuccessProvider
     * @covers Phramework\Validate\ArrayValidator::validate
     */
    public function testValidateSuccess($input)
    {
        $return = $this->object->validate($input);

        $this->assertInternalType('array', $return->value);
        $this->assertTrue($return->status);
    }

    /**
     * @dataProvider validateFailureProvider
     * @covers Phramework\Validate\ArrayValidator::validate
     */
    public function testValidateFailure($input)
    {
        $return = $this->object->validate($input);

        $this->assertFalse($return->status);
    }

    /**
     * @covers Phramework\Validate\ArrayValidator::getType
     */
    public function testGetType()
    {
        $this->assertEquals('array', $this->object->getType());
    }
}
