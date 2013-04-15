<?php

namespace Datagram;

use React\EventLoop\LoopInterface;
use Evenement\EventEmitter;

/** @event message */
// interface similar to Stream
class Socket extends EventEmitter
{
    protected $loop;
    protected $socket;
    protected $address;

    public $bufferSize = 65536;

    public function __construct(LoopInterface $loop, $socket, $address)
    {
        $this->loop = $loop;
        $this->socket = $socket;
        $this->address = $address;

        $this->resume();
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getPort()
    {
        return (int)substr($this->address, strrpos($this->address, ':') + 1);
    }

    public function getHost()
    {
        return trim(substr($this->address, 0, strrpos($this->address, ':')), '[]');
    }

    public function send($data, $target = null)
    {
        if ($target === null) {
            $target = $this->address;
        }
        stream_socket_sendto($this->socket, $data, 0, $target);
    }


    public function pause()
    {
        $this->loop->removeReadStream($this->socket);
    }

    public function resume()
    {
        $this->loop->addReadStream($this->socket, array($this, 'onReceive'));
    }

    public function onReceive($message)
    {
        $data = stream_socket_recvfrom($this->socket, $this->bufferSize, 0, $peer);

        if ($data === false) {
            // receiving data failed => remote side rejected one of our packets
            // due to the nature of UDP, there's no way to tell which one exactly
            // $peer is not filled either

            // emit error message and local socket
            $this->emit('error', array(new \Exception('Invalid message'), $this));
            return;
        }

        $this->emit('message', array($data, $this->sanitizeAddress($peer)));
    }

    public function close()
    {
        $this->pause();
        fclose($this->socket);
        $this->socket = false;
    }

    private function sanitizeAddress($address)
    {
        // doc comment suggests IPv6 address is not enclosed in square brackets?

        $pos = strrpos(':', $address);
        // this is an IPv6 address which includes colons but no square brackets
        if ($pos !== false && substr($address, 0, 1) !== '[') {
            if (strpos(':', $address) < $pos) {
                $port = substr($address, $pos + 1);
                $address = '[' . substr($address, 0, $pos) . ']:' . $port;
            }

        }
        return $address;
    }

    public function __toString()
    {
        return $this->address . ' (' . $this->socket . ')';
    }
}
