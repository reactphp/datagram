<?php

require __DIR__ . '/../vendor/autoload.php';

abstract class TestCase extends PHPUnit_Framework_TestCase
{
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
        return $this->getMockBuilder('CallableStub')->getMock();
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
