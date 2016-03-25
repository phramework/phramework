<?php

namespace Phramework\Route;

/**
 */
class ClassBasedTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ClassBased
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new ClassBased([], [], []);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->markTestIncomplete();
    }

    /**
     * @covers Phramework\URIStrategy\ClassBased::invoke
     * @todo   Implement testInvoke().
     */
    public function testInvoke()
    {
        $this->markTestIncomplete();
    }
}
