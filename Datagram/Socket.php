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

    public $bufferSize = 65536;

    public function __construct(LoopInterface $loop, $socket)
    {
        $this->loop = $loop;
        $this->socket = $socket;

        $this->resume();
    }

    public function getAddress()
    {
        return stream_socket_get_name($this->socket, false);
    }

    public function getPort()
    {
        $address = $this->getAddress();
        return (int)substr($address, strrpos($address, ':') + 1);
    }

    public function getHost()
    {
        $address = $this->getAddress();
        return trim(substr($address, 0, strrpos($address, ':')), '[]');
    }

    public function send($data, $target = null)
    {
        if ($target === null) {
            fwrite($this->socket, $data);
        } else {
            stream_socket_sendto($this->socket, $data, 0, $target);
        }
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
        return $this->getAddress() . ' (' . $this->socket . ')';
    }
}
