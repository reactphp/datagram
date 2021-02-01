<?php

namespace React\Tests\Datagram;

use PHPUnit\Framework\TestCase;
use React\Datagram\Buffer;

class BufferTest extends TestCase
{
    public function testSendAddsSocketToLoop()
    {
        $socket = stream_socket_client('udp://127.0.0.1:8000');

        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('addWriteStream')->with($socket);

        $client = new Buffer($loop, $socket);

        $client->send('foo');
    }

    public function testSendAfterCloseIsNoop()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->never())->method('addWriteStream');

        $socket = stream_socket_client('udp://127.0.0.1:8000');

        $client = new Buffer($loop, $socket);

        $client->close();
        $client->send('foo');
    }

    public function testCloseAfterSendAddsSocketToLoopRemovesSocketFromLoopAgain()
    {
        $socket = stream_socket_client('udp://127.0.0.1:8000');

        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('addWriteStream')->with($socket);
        $loop->expects($this->once())->method('removeWriteStream')->with($socket);

        $client = new Buffer($loop, $socket);

        $client->send('foo');
        $client->close();
    }

    public function testCloseTwiceEmitsCloseEventOnce()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $socket = stream_socket_client('udp://127.0.0.1:8000');

        $client = new Buffer($loop, $socket);

        $closed = 0;
        $client->on('close', function () use (&$closed) {
            ++$closed;
        });

        $this->assertEquals(0, $closed);

        $client->close();

        $this->assertEquals(1, $closed);

        $client->close();

        $this->assertEquals(1, $closed);
    }
}
