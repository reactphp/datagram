<?php

use Datagram\Socket;
use React\Promise\When;
use React\Promise\PromiseInterface;

class FactoryTest extends TestCase
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
}
