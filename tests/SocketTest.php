<?php

namespace React\Tests\Datagram;

use React\Datagram\Socket;

class SocketTest extends TestCase
{
    private $loop;
    private $factory;

    /**
     * @before
     */
    public function setUpFactory()
    {
        $this->loop = \React\EventLoop\Factory::create();
        $this->factory = new \React\Datagram\Factory($this->loop, $this->createResolverMock());
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testCreateClientCloseWillNotBlock()
    {
        $promise = $this->factory->createClient('127.0.0.1:12345');
        $client = \React\Async\await($promise);

        $client->send('test');
        $client->close();

        $this->loop->run();

        return $client;
    }

    /**
     * @doesNotPerformAssertions
     * @param Socket $client
     * @depends testCreateClientCloseWillNotBlock
     */
    public function testClientCloseAgainWillNotBlock(Socket $client)
    {
        $client->close();
        $this->loop->run();
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testCreateClientEndWillNotBlock()
    {
        $promise = $this->factory->createClient('127.0.0.1:12345');
        $client = \React\Async\await($promise);

        $client->send('test');
        $client->end();

        $this->loop->run();

        return $client;
    }

    /**
     * @doesNotPerformAssertions
     * @param Socket $client
     * @depends testCreateClientEndWillNotBlock
     */
    public function testClientEndAgainWillNotBlock(Socket $client)
    {
        $client->end();
        $this->loop->run();

        return $client;
    }

    /**
     * @doesNotPerformAssertions
     * @param Socket $client
     * @depends testClientEndAgainWillNotBlock
     */
    public function testClientSendAfterEndIsNoop(Socket $client)
    {
        $client->send('does not matter');
        $this->loop->run();
    }

    public function testClientSendHugeWillFailWithoutCallingCustomErrorHandler()
    {
        $promise = $this->factory->createClient('127.0.0.1:12345');
        $client = \React\Async\await($promise);

        $client->send(str_repeat(1, 1024 * 1024));
        $client->on('error', $this->expectCallableOnce());
        $client->end();

        $error = null;
        set_error_handler(function ($_, $errstr) use (&$error) {
            $error = $errstr;
        });

        $this->loop->run();

        restore_error_handler();
        $this->assertNull($error);
    }

    public function testClientSendNoServerWillFail()
    {
        $promise = $this->factory->createClient('127.0.0.1:1234');
        $client = \React\Async\await($promise);

        // send a message to a socket that is not actually listening
        // expect the remote end to reject this by sending an ICMP message
        // which we will receive as an error message. This depends on the
        // host to actually reject UDP datagrams, which not all systems do.
        $client->send('hello');
        $client->on('error', $this->expectCallableOnce());

        $loop = $this->loop;
        $client->on('error', function () use ($loop) {
             $loop->stop();
        });

        $that = $this;
        $this->loop->addTimer(1.0, function () use ($that, $loop) {
            $loop->stop();
            $that->markTestSkipped('UDP packet was not rejected after 0.5s, ignoring test');
        });

        $this->loop->run();
    }

    public function testCreatePair()
    {
        $promise = $this->factory->createServer('127.0.0.1:0');
        $server = \React\Async\await($promise);

        $promise = $this->factory->createClient($server->getLocalAddress());
        $client = \React\Async\await($promise);

        $that = $this;
        $server->on('message', function ($message, $remote, $server) use ($that) {
            $that->assertEquals('test', $message);

            // once the server receives a message, send it pack to client and stop server
            $server->send('response:' . $message, $remote);
            $server->end();
        });

        $client->on('message', function ($message, $remote, $client) use ($that) {
            $that->assertEquals('response:test', $message);

            // once the client receives a message, stop client
            $client->end();
        });

        $client->send('test');

        $this->loop->run();
    }

    public function provideSanitizeAddress()
    {
        return array(
            array(
                '127.0.0.1:1337',
            ),
            array(
                '[::1]:1337',
            ),
        );
    }

    /**
     * @dataProvider provideSanitizeAddress
     */
    public function testSanitizeAddress($address)
    {
        $promise = $this->factory->createServer($address);

        try {
            $server = \React\Async\await($promise);
        } catch (\Exception $e) {
            if (strpos($address, '[') === false) {
                throw $e;
            }

            $this->markTestSkipped('Unable to start IPv6 server socket (IPv6 not supported on this system?)');
        }

        $promise = $this->factory->createClient($server->getLocalAddress());
        $client = \React\Async\await($promise);

        $that = $this;
        $server->on('message', function ($message, $remote, $server) use ($that) {
            // once the server receives a message, send it pack to client and stop server
            $server->send('response:' . $message, $remote);
            $server->end();
        });

        $client->on('message', function ($message, $remote, $client) use ($that, $address) {
            $that->assertEquals($address, $remote);

            // once the client receives a message, stop client
            $client->end();
        });

        $client->send('test');

        $this->loop->run();
    }
}
