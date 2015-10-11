<?php

namespace Phramework\Validate;

class URLTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var URL
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new URL(3, 100);
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
        //input, expected
        return [
            ['https://nohponex.gr'],
            ['http://www.thmmy.gr/dir/file.php?param=ok&second=false#ok'],
            ['http://127.0.0.1/app']
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            ['100'],
            [540],
            ['nx@ma.il'],
            ['nohponex@gmailcom'],
            ['http::://nohponex.gr'],
            ['nohponex.gr'],
            ['nohponex'],
            ['//nohponex.gr']
        ];
    }

    /**
     * @dataProvider validateSuccessProvider
     * @covers Phramework\Validate\URL::validate
     */
    public function testValidateSuccess($input)
    {
        $return = $this->object->validate($input);

        $this->assertInternalType('string', $return->value);
        $this->assertTrue($return->status);
    }

    /**
     * @dataProvider validateFailureProvider
     * @covers Phramework\Validate\URL::validate
     */
    public function testValidateFailure($input)
    {
        $return = $this->object->validate($input);

        $this->assertFalse($return->status);
    }

    /**
     * @covers Phramework\Validate\URL::getType
     */
    public function testGetType()
    {
        $this->assertEquals('url', $this->object->getType());
    }
}
