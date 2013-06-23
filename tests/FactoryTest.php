<?php

use React\Promise\When;

use React\Promise\PromiseInterface;

require __DIR__.'/../vendor/autoload.php';

class FactoryTest extends PHPUnit_Framework_TestCase
{
    private $factory;

    public function testClientSuccess()
    {
        $loop = React\EventLoop\Factory::create();
        $factory = new Datagram\Factory($loop, $this->createResolverMock());

        $promise = $factory->createClient('127.0.0.1', 12345);
        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);

        $capturedClient = null;
        $promise->then(function ($client) use (&$capturedClient, $loop) {
            $capturedClient = $client;
            $loop->stop();
        }, $this->expectCallableNever());

        // future-turn resolutions are not enforced, so the client MAY be known here already
        if ($capturedClient === null) {
            $loop->run();
        }

        $this->assertInstanceOf('Datagram\Socket', $capturedClient);

        $capturedClient->close();
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
        return $this->getMock('React\Tests\Socket\Stub\CallableStub');
    }

    private function createResolverMock()
    {
        return $this->getMockBuilder('React\Dns\Resolver\Resolver')
        ->disableOriginalConstructor()
        ->getMock();
    }
}
