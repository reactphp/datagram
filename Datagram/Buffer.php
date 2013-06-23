<?php

namespace Datagram;

use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use \Exception;

class Buffer extends EventEmitter
{
    private $loop;
    private $socket;
    private $listening = false;
    private $outgoing = array();
    private $writable = true;

    public function __construct(LoopInterface $loop, $socket)
    {
        $this->loop = $loop;
        $this->socket = $socket;
    }

    public function send($data, $remoteAddress = null)
    {
        if ($this->writable === false) {
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
            // do not use fwrite() as it obeys the stream buffer size and
            // packets are not to be split at 8kb
            $ret = @stream_socket_sendto($this->socket, $data);
        } else {
            $ret = @stream_socket_sendto($this->socket, $data, 0, $remoteAddress);
        }

        if ($ret < 0) {
            $error = error_get_last();
            $message = 'Unable to send packet: ' . trim($error['message']);
            $this->emit('error', array(new Exception($message)));
        }

        if (!$this->outgoing) {
            $this->loop->removeWriteStream($this->socket);
            $this->listening = false;

            if (!$this->writable) {
                $this->close();
            }
        }
    }

    public function close()
    {
        if ($this->socket === false) {
            return false;
        }

        $this->emit('close', array($this));

        if ($this->listening) {
            $this->loop->removeWriteStream($this->socket);
            $this->listening = false;
        }

        $this->writable = false;
        $this->socket = false;
        $this->outgoing = array();
        $this->removeAllListeners();
    }

    public function end()
    {
        if ($this->writable === false) {
            return;
        }

        $this->writable = false;

        if (!$this->listening) {
            $this->close();
        }
    }
}
