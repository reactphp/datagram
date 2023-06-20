<?php

namespace React\Tests\Datagram;

use React\Datagram\Factory;
use React\Datagram\Socket;
use React\Promise;

class FactoryTest extends TestCase
{
    private $loop;
    private $resolver;
    private $factory;

    /**
     * @before
     */
    public function setUpFactory()
    {
        $this->loop = \React\EventLoop\Factory::create();
        $this->resolver = $this->createResolverMock();
        $this->factory = new Factory($this->loop, $this->resolver);
    }

    public function testConstructWithoutLoopAssignsLoopAutomatically()
    {
        $factory = new Factory();

        $ref = new \ReflectionProperty($factory, 'loop');
        $ref->setAccessible(true);
        $loop = $ref->getValue($factory);

        $this->assertInstanceOf('React\EventLoop\LoopInterface', $loop);
    }

    public function testCreateClient()
    {
        $this->resolver->expects($this->never())->method('resolve');

        $promise = $this->factory->createClient('127.0.0.1:12345');

        $capturedClient = \React\Async\await($promise);
        $this->assertInstanceOf('React\Datagram\Socket', $capturedClient);

        $this->assertEquals('127.0.0.1:12345', $capturedClient->getRemoteAddress());

        $this->assertContainsString('127.0.0.1:', $capturedClient->getLocalAddress());
        $this->assertNotEquals('127.0.0.1:12345', $capturedClient->getLocalAddress());

        $capturedClient->close();

        $this->assertNull($capturedClient->getRemoteAddress());
    }

    public function testCreateClientLocalhost()
    {
        $this->resolver->expects($this->once())->method('resolve')->with('localhost')->willReturn(Promise\resolve('127.0.0.1'));

        $promise = $this->factory->createClient('localhost:12345');

        $capturedClient = \React\Async\await($promise);
        $this->assertInstanceOf('React\Datagram\Socket', $capturedClient);

        $this->assertEquals('127.0.0.1:12345', $capturedClient->getRemoteAddress());

        $this->assertContainsString('127.0.0.1:', $capturedClient->getLocalAddress());
        $this->assertNotEquals('127.0.0.1:12345', $capturedClient->getLocalAddress());

        $capturedClient->close();
    }

    public function testCreateClientLocalhostWithDefaultResolver()
    {
        $this->resolver = null;
        $this->factory = new Factory($this->loop);

        $promise = $this->factory->createClient('localhost:12345');

        $capturedClient = \React\Async\await($promise);
        $this->assertInstanceOf('React\Datagram\SocketInterface', $capturedClient);

        $capturedClient->close();
    }

    public function testCreateClientIpv6()
    {
        $promise = $this->factory->createClient('[::1]:12345');

        try {
            $capturedClient = \React\Async\await($promise);
        } catch (\Exception $e) {
            $this->markTestSkipped('Unable to start IPv6 client socket (IPv6 not supported on this system?)');
        }

        $this->assertInstanceOf('React\Datagram\Socket', $capturedClient);

        $this->assertEquals('[::1]:12345', $capturedClient->getRemoteAddress());

        $this->assertContainsString('[::1]:', $capturedClient->getLocalAddress());
        $this->assertNotEquals('[::1]:12345', $capturedClient->getLocalAddress());

        $capturedClient->close();
    }

    public function testCreateServer()
    {
        $promise = $this->factory->createServer('127.0.0.1:12345');

        $capturedServer = \React\Async\await($promise);
        $this->assertInstanceOf('React\Datagram\Socket', $capturedServer);

        $this->assertEquals('127.0.0.1:12345', $capturedServer->getLocalAddress());
        $this->assertNull($capturedServer->getRemoteAddress());

        $capturedServer->close();

        $this->assertNull($capturedServer->getLocalAddress());
    }

    public function testCreateServerRandomPort()
    {
        $promise = $this->factory->createServer('127.0.0.1:0');

        $capturedServer = \React\Async\await($promise);
        $this->assertInstanceOf('React\Datagram\Socket', $capturedServer);

        $this->assertNotEquals('127.0.0.1:0', $capturedServer->getLocalAddress());
        $this->assertNull($capturedServer->getRemoteAddress());

        $capturedServer->close();
    }

    public function testCreateClientWithIpWillNotUseResolver()
    {
        $this->resolver->expects($this->never())->method('resolve');

        $client = \React\Async\await($this->factory->createClient('127.0.0.1:0'));
        $client->close();
    }

    public function testCreateClientWithHostnameWillUseResolver()
    {
        $this->resolver->expects($this->once())->method('resolve')->with('example.com')->willReturn(Promise\resolve('127.0.0.1'));

        $client = \React\Async\await($this->factory->createClient('example.com:0'));
        $client->close();
    }

    public function testCreateClientWithHostnameWillRejectIfResolverRejects()
    {
        $this->resolver->expects($this->once())->method('resolve')->with('example.com')->willReturn(Promise\reject(new \RuntimeException('test')));

        $this->setExpectedException('RuntimeException');
        \React\Async\await($this->factory->createClient('example.com:0'));
    }

    public function testCreateClientWithInvalidHostnameWillReject()
    {
        $this->setExpectedException('Exception', 'Unable to create client socket');
        \React\Async\await($this->factory->createClient('/////'));
    }

    public function testCreateServerWithInvalidHostnameWillReject()
    {
        $this->setExpectedException('Exception', 'Unable to create server socket');
        \React\Async\await($this->factory->createServer('/////'));
    }

    public function testCancelCreateClientWithCancellableHostnameResolver()
    {
        $promise = new Promise\Promise(function () { }, $this->expectCallableOnce());
        $this->resolver->expects($this->once())->method('resolve')->with('example.com')->willReturn($promise);

        $promise = $this->factory->createClient('example.com:0');
        $promise->cancel();

        $this->setExpectedException('RuntimeException');
        \React\Async\await($promise);
    }

    public function testCancelCreateClientWithUncancellableHostnameResolver()
    {
        $promise = $this->getMockBuilder('React\Promise\PromiseInterface')->getMock();
        $this->resolver->expects($this->once())->method('resolve')->with('example.com')->willReturn($promise);

        $promise = $this->factory->createClient('example.com:0');
        $promise->cancel();

        $this->setExpectedException('RuntimeException');
        \React\Async\await($promise);
    }
}
