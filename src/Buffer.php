<?php

namespace React\Datagram;

use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use \Exception;

class Buffer extends EventEmitter
{
    protected $loop;
    protected $socket;

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
            $this->handleResume();
            $this->listening = true;
        }
    }

    public function onWritable()
    {
        list($data, $remoteAddress) = \array_shift($this->outgoing);

        try {
            $this->handleWrite($data, $remoteAddress);
        }
        catch (Exception $e) {
            $this->emit('error', array($e, $this));
        }

        if (!$this->outgoing) {
            if ($this->listening) {
                $this->handlePause();
                $this->listening = false;
            }

            if (!$this->writable) {
                $this->close();
            }
        }
    }

    public function close()
    {
        if ($this->socket === false) {
            return;
        }

        $this->emit('close', array($this));

        if ($this->listening) {
            $this->handlePause();
            $this->listening = false;
        }

        $this->writable = false;
        $this->socket = false;
        $this->outgoing = array();
        $this->removeAllListeners();
    }

    public function end()
    {
        $this->writable = false;

        if (!$this->outgoing) {
            $this->close();
        }
    }

    protected function handlePause()
    {
        $this->loop->removeWriteStream($this->socket);
    }

    protected function handleResume()
    {
        $this->loop->addWriteStream($this->socket, array($this, 'onWritable'));
    }

    protected function handleWrite($data, $remoteAddress)
    {
        $errstr = '';
        \set_error_handler(function ($_, $error) use (&$errstr) {
            // Match errstr from PHP's warning message.
            // stream_socket_sendto(): Message too long\n
            $errstr = \trim($error);
        });

        if ($remoteAddress === null) {
            // do not use fwrite() as it obeys the stream buffer size and
            // packets are not to be split at 8kb
            $ret = \stream_socket_sendto($this->socket, $data);
        } else {
            $ret = \stream_socket_sendto($this->socket, $data, 0, $remoteAddress);
        }

        \restore_error_handler();

        if ($ret < 0 || $ret === false) {
            throw new Exception('Unable to send packet: ' . $errstr);
        }
    }
}
