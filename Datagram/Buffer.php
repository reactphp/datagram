<?php

namespace Datagram;

use React\EventLoop\LoopInterface;

class Buffer
{
    private $loop;
    private $socket;
    private $listening = false;
    private $outgoing = array();

    public function __construct(LoopInterface $loop, $socket)
    {
        $this->loop = $loop;
        $this->socket = $socket;
    }

    public function send($data, $remoteAddress = null)
    {
        if ($this->socket === false) {
            return;
        }

        $this->outgoing []= array($data, $remoteAddress);

        if (!$this->listening) {
            $this->loop->addWriteStream($this->socket, array($this, 'handleWrite'));
            $this->listening = true;
        }
    }

    public function handleWrite()
    {
        list($data, $remoteAddress) = array_shift($this->outgoing);

        if ($remoteAddress === null) {
            fwrite($this->socket, $data);
        } else {
            stream_socket_sendto($this->socket, $data, 0, $remoteAddress);
        }

        if (!$this->outgoing) {
            $this->loop->removeWriteStream($this->socket);
            $this->listening = false;
        }
    }

    public function close()
    {
        if ($this->socket === false) {
            return false;
        }

        if ($this->listening) {
            $this->loop->removeWriteStream($this->socket);
            $this->listening = false;
        }

        $this->socket = false;
        $this->outgoing = array();
    }
}
