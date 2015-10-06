<?php

namespace Phramework\Validate;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-10-05 at 22:11:07.
 */
class ObjectTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Object
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $properties = [
            'str' => new \Phramework\Validate\String(2,4),
            'ok' => new \Phramework\Validate\Boolean(),
        ];

        $this->object = new Object($properties, ['ok']);
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
            [['ok' => true]],
            [(object)['ok' => 'true', 'okk' => '123']],
            [(object)['ok' => false, 'okk' => 'xyz' ]]
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            [1], //not an array or object
            [['ok']], //`ok` is not an object key
            [['abc']],
            [(object)(['okk' => 'hello'])], //because missing ok
            [['ok'=> 'omg', 'okk' => '2', 'xyz' => true]], //because of ok is not boolean
        ];
    }

    /**
     * @dataProvider validateSuccessProvider
     * @covers Phramework\Validate\Object::validate
     */
    public function testValidateSuccess($input)
    {
        $return = $this->object->validate($input);

        $this->assertEquals(true, $return->status);
        $this->assertInternalType('object', $return->value);

    }

    /**
     * @dataProvider validateFailureProvider
     * @covers Phramework\Validate\Object::validate
     */
    public function testValidateFailure($input)
    {
        $return = $this->object->validate($input);

        $this->assertEquals(false, $return->status);
    }

    /**
      * @covers Phramework\Validate\Object::addProperties
     */
    public function testAddPropertiesSuccess()
    {
        $originalPropertiesCount = count($this->object->properties);
        $properties = ['new_property' => new object];
        $this->object->addProperties($properties);

        //Test if number of properties is increased by count of added properties
        $this->assertEquals(
            $originalPropertiesCount + count($properties),
            count($this->object->properties)
        );
    }

    /**
      * @covers Phramework\Validate\Object::addProperties
      * @expectedException Exception
     */
    public function testAddPropertiesFailure()
    {
        $properties = new Object();
        $this->object->addProperties($properties); //Not an array
    }

    /**
      * @covers Phramework\Validate\Object::addProperty
     */
    public function testAddPropertySuccess()
    {
        $key = 'my_key';
        $property = new Object();
        $this->object->addProperty($key, $property);

        $this->assertTrue(
            array_key_exists($key, $this->object->properties)
        );
    }

    /**
      * @covers Phramework\Validate\Object::addProperty
      * @expectedException Exception
     */
    public function testAddPropertyFailure()
    {
        $property = new Object();
        $this->object->addProperty('new', $property);

        $this->object->addProperty('new', $property); //With same key
    }

    /**
      * @covers Phramework\Validate\Object::addProperty
      * @expectedException PHPUnit_Framework_Error
     */
    public function testAddPropertyFailureInvalidType()
    {
        $property = new Object();
        $this->object->addProperty('new', ['hello' => 'world']);
    }

    /**
     * @covers Phramework\Validate\Object::getType
     */
    public function testGetType()
    {
        $this->assertEquals('object', $this->object->getType());
    }
}
