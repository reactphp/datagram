<?php

use React\Promise\When;
use React\Promise\PromiseInterface;

require __DIR__.'/../vendor/autoload.php';

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    protected function getValueFromResolvedPromise($promise)
    {
        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);

        $loop = $this->loop;
        $capturedValue = null;
        $promise->then(function ($value) use (&$capturedValue, $loop) {
            $capturedValue = $value;
            $loop->stop();
        }, $this->expectCallableNever());

        // future-turn resolutions are not enforced, so the value MAY be known here already
        if ($capturedValue === null) {
            $loop->run();
        }

        return $capturedValue;
    }

    protected function expectCallableOnce()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke');

        return $mock;
    }

    protected function expectCallableNever()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->never())
            ->method('__invoke');

        return $mock;
    }

    protected function createCallableMock()
    {
        return $this->getMock('CallableStub');
    }

    protected function createResolverMock()
    {
        return $this->getMockBuilder('React\Dns\Resolver\Resolver')
            ->disableOriginalConstructor()
            ->getMock();
    }
}

class CallableStub
{
    public function __invoke()
    {
    }
}
