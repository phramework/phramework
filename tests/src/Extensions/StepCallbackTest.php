<?php

namespace Phramework\Extensions;

class StepCallbackTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var StepCallback
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new StepCallback();

        $this->object->addVariable('key', 'value');
    }

    /**
     * @covers Phramework\Extensions\StepCallback::addVariable
     */
    public function testAddVariable()
    {
        $this->object->addVariable('key', 'value');
    }

    /**
     * @covers Phramework\Extensions\StepCallback::add
     * @expectedException Exception
     */
    public function testAddFailureIvalidStep()
    {
        $this->object->add(
            'INVALID_STEP',
            function () {
                echo 'Unreached';
            }
        );
    }

    /**
     * @covers Phramework\Extensions\StepCallback::add
     * @expectedException Exception
     */
    public function testAddFailureIvalidCallback()
    {
        $this->object->add(
            StepCallback::STEP_AFTER_AUTHENTICATION_CHECK,
            (new \stdClass())
        );
    }

    /**
     * @covers Phramework\Extensions\StepCallback::call
     */
    public function testCall()
    {
        $params  = ['changed' => false];
        $headers = ['Content-Type' => 'application/json'];

        $test = $this;

        $this->object->add(
            StepCallback::STEP_AFTER_CALL_ROUTE,
            function (
                $step,
                &$params,
                $method,
                &$headers,
                $callbackVariables,
                $invokedController,
                $invokedMethod
            ) use ($test) {
                $test->assertSame($step, StepCallback::STEP_AFTER_CALL_ROUTE);
                $test->assertInternalType('array', $params);
                $test->assertInternalType('string', $method);
                $test->assertInternalType('array', $headers);
                $test->assertInternalType('array', $callbackVariables);
                $test->assertSame('value', $callbackVariables['key']);
                $test->assertSame('controller', $invokedController);
                $test->assertSame('GET', $invokedMethod);

                $params['changed'] = true;

                unset($headers['Content-Type']);
            }
        );

        $this->object->call(
            StepCallback::STEP_AFTER_CALL_ROUTE,
            $params,
            \Phramework\Phramework::METHOD_GET,
            $headers,
            ['controller', 'GET']
        );

        $this->assertSame(
            true,
            $params['changed'],
            'Check if we are able to modify the contents of $params'
        );

        $this->assertSame(
            0,
            count($headers),
            'Check if we are able to modify the contents of $headers'
        );
    }
}
