<?php

use React\Datagram\Socket;
use React\Datagram\Factory;
use Clue\React\Block;
use React\Promise;

class FactoryTest extends TestCase
{
    private $loop;
    private $resolver;
    private $factory;

    public function setUp()
    {
        $this->loop = React\EventLoop\Factory::create();
        $this->resolver = $this->createResolverMock();
        $this->factory = new Factory($this->loop, $this->resolver);
    }

    public function testCreateClient()
    {
        $promise = $this->factory->createClient('127.0.0.1:12345');

        $capturedClient = Block\await($promise, $this->loop);
        $this->assertInstanceOf('React\Datagram\Socket', $capturedClient);

        $this->assertEquals('127.0.0.1:12345', $capturedClient->getRemoteAddress());

        $this->assertContains('127.0.0.1:', $capturedClient->getLocalAddress());
        $this->assertNotEquals('127.0.0.1:12345', $capturedClient->getLocalAddress());

        $capturedClient->close();

        $this->assertNull($capturedClient->getRemoteAddress());
    }

    public function testCreateClientLocalhost()
    {
        $promise = $this->factory->createClient('localhost:12345');

        $capturedClient = Block\await($promise, $this->loop);
        $this->assertInstanceOf('React\Datagram\Socket', $capturedClient);

        $this->assertEquals('127.0.0.1:12345', $capturedClient->getRemoteAddress());

        $this->assertContains('127.0.0.1:', $capturedClient->getLocalAddress());
        $this->assertNotEquals('127.0.0.1:12345', $capturedClient->getLocalAddress());

        $capturedClient->close();
    }

    public function testCreateClientIpv6()
    {
        $promise = $this->factory->createClient('[::1]:12345');

        try {
            $capturedClient = Block\await($promise, $this->loop);
        } catch (\Exception $e) {
            $this->markTestSkipped('Unable to start IPv6 client socket (IPv6 not supported on this system?)');
        }

        $this->assertInstanceOf('React\Datagram\Socket', $capturedClient);

        $this->assertEquals('[::1]:12345', $capturedClient->getRemoteAddress());

        $this->assertContains('[::1]:', $capturedClient->getLocalAddress());
        $this->assertNotEquals('[::1]:12345', $capturedClient->getLocalAddress());

        $capturedClient->close();
    }

    public function testCreateServer()
    {
        $promise = $this->factory->createServer('127.0.0.1:12345');

        $capturedServer = Block\await($promise, $this->loop);
        $this->assertInstanceOf('React\Datagram\Socket', $capturedServer);

        $this->assertEquals('127.0.0.1:12345', $capturedServer->getLocalAddress());
        $this->assertNull($capturedServer->getRemoteAddress());

        $capturedServer->close();

        $this->assertNull($capturedServer->getLocalAddress());
    }

    public function testCreateServerRandomPort()
    {
        $promise = $this->factory->createServer('127.0.0.1:0');

        $capturedServer = Block\await($promise, $this->loop);
        $this->assertInstanceOf('React\Datagram\Socket', $capturedServer);

        $this->assertNotEquals('127.0.0.1:0', $capturedServer->getLocalAddress());
        $this->assertNull($capturedServer->getRemoteAddress());

        $capturedServer->close();
    }

    public function testCreateClientWithIpWillNotUseResolver()
    {
        $this->resolver->expects($this->never())->method('resolve');

        $client = Block\await($this->factory->createClient('127.0.0.1:0'), $this->loop);
        $client->close();
    }

    public function testCreateClientWithHostnameWillUseResolver()
    {
        $this->resolver->expects($this->once())->method('resolve')->with('example.com')->willReturn(Promise\resolve('127.0.0.1'));

        $client = Block\await($this->factory->createClient('example.com:0'), $this->loop);
        $client->close();
    }

    public function testCreateClientWithHostnameWillRejectIfResolverRejects()
    {
        $this->resolver->expects($this->once())->method('resolve')->with('example.com')->willReturn(Promise\reject(new \RuntimeException('test')));

        $this->setExpectedException('RuntimeException');
        Block\await($this->factory->createClient('example.com:0'), $this->loop);
    }

    public function testCreateClientWithHostnameWillRejectIfNoResolverIsGiven()
    {
        $this->factory = new Factory($this->loop);

        $this->setExpectedException('Exception');
        Block\await($this->factory->createClient('example.com:0'), $this->loop);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Unable to create client socket
     */
    public function testCreateClientWithInvalidHostnameWillReject()
    {
        Block\await($this->factory->createClient('/////'), $this->loop);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Unable to create server socket
     */
    public function testCreateServerWithInvalidHostnameWillReject()
    {
        Block\await($this->factory->createServer('/////'), $this->loop);
    }

    public function testCancelCreateClientWithCancellableHostnameResolver()
    {
        $promise = new Promise\Promise(function () { }, $this->expectCallableOnce());
        $this->resolver->expects($this->once())->method('resolve')->with('example.com')->willReturn($promise);

        $promise = $this->factory->createClient('example.com:0');
        $promise->cancel();

        $this->setExpectedException('RuntimeException');
        Block\await($promise, $this->loop);
    }

    public function testCancelCreateClientWithUncancellableHostnameResolver()
    {
        $promise = $this->getMock('React\Promise\PromiseInterface');
        $this->resolver->expects($this->once())->method('resolve')->with('example.com')->willReturn($promise);

        $promise = $this->factory->createClient('example.com:0');
        $promise->cancel();

        $this->setExpectedException('RuntimeException');
        Block\await($promise, $this->loop);
    }
}
