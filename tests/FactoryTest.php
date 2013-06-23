<?php

use Datagram\Socket;

use React\Promise\When;

use React\Promise\PromiseInterface;

require __DIR__.'/../vendor/autoload.php';

class FactoryTest extends PHPUnit_Framework_TestCase
{
    private $factory;

    public function setUp()
    {
        $this->loop = React\EventLoop\Factory::create();
        $this->factory = new Datagram\Factory($this->loop, $this->createResolverMock());
    }

    public function testCreateClient()
    {
        $promise = $this->factory->createClient('127.0.0.1', 12345);

        $capturedClient = $this->getValueFromResolvedPromise($promise);
        $this->assertInstanceOf('Datagram\Socket', $capturedClient);

        $capturedClient->close();
    }

    public function testCreateClientLocalhost()
    {
        $promise = $this->factory->createClient('localhost', 12345);

        $capturedClient = $this->getValueFromResolvedPromise($promise);
        $this->assertInstanceOf('Datagram\Socket', $capturedClient);

        $capturedClient->close();
    }

    public function testCreateClientIpv6()
    {
        $promise = $this->factory->createClient('::1', 12345);

        $capturedClient = $this->getValueFromResolvedPromise($promise);
        $this->assertInstanceOf('Datagram\Socket', $capturedClient);

        $capturedClient->close();
    }

    public function testCreateServer()
    {
        $promise = $this->factory->createServer(12345, '127.0.0.1');

        $capturedServer = $this->getValueFromResolvedPromise($promise);
        $this->assertInstanceOf('Datagram\Socket', $capturedServer);

        $capturedServer->close();
    }

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
        return $this->getMock('React\Tests\Socket\Stub\CallableStub');
    }

    private function createResolverMock()
    {
        return $this->getMockBuilder('React\Dns\Resolver\Resolver')
        ->disableOriginalConstructor()
        ->getMock();
    }
}
