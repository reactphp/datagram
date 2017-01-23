<?php

namespace React\Datagram;

use React\EventLoop\LoopInterface;
use Evenement\EventEmitter;
use Exception;

class Socket extends EventEmitter implements SocketInterface
{
    protected $loop;
    protected $socket;

    protected $buffer;

    public $bufferSize = 65536;

    public function __construct(LoopInterface $loop, $socket, Buffer $buffer = null)
    {
        $this->loop = $loop;
        $this->socket = $socket;

        if ($buffer === null) {
            $buffer = new Buffer($loop, $socket);
        }
        $this->buffer = $buffer;

        $that = $this;
        $this->buffer->on('error', function ($error) use ($that) {
            $that->emit('error', array($error, $that));
        });
        $this->buffer->on('close', array($this, 'close'));

        $this->resume();
    }

    public function getLocalAddress()
    {
        return $this->sanitizeAddress(@stream_socket_get_name($this->socket, false));
    }

    public function getRemoteAddress()
    {
        return $this->sanitizeAddress(@stream_socket_get_name($this->socket, true));
    }

    public function send($data, $remoteAddress = null)
    {
        $this->buffer->send($data, $remoteAddress);
    }

    public function pause()
    {
        $this->loop->removeReadStream($this->socket);
    }

    public function resume()
    {
        if ($this->socket !== false) {
            $this->loop->addReadStream($this->socket, array($this, 'onReceive'));
        }
    }

    public function onReceive()
    {
        try {
            $data = $this->handleReceive($peer);
        }
        catch (Exception $e) {
            // emit error message and local socket
            $this->emit('error', array($e, $this));
            return;
        }

        $this->emit('message', array($data, $peer, $this));
    }

    public function close()
    {
        if ($this->socket === false) {
            return;
        }

        $this->emit('close', array($this));
        $this->pause();

        $this->handleClose();
        $this->socket = false;
        $this->buffer->close();

        $this->removeAllListeners();
    }

    public function end()
    {
        $this->buffer->end();
    }

    private function sanitizeAddress($address)
    {
        if ($address === false) {
            return null;
        }

        // this is an IPv6 address which includes colons but no square brackets
        $pos = strrpos($address, ':');
        if ($pos !== false && strpos($address, ':') < $pos && substr($address, 0, 1) !== '[') {
            $port = substr($address, $pos + 1);
            $address = '[' . substr($address, 0, $pos) . ']:' . $port;
        }
        return $address;
    }

    protected function handleReceive(&$peerAddress)
    {
        $data = stream_socket_recvfrom($this->socket, $this->bufferSize, 0, $peerAddress);

        if ($data === false) {
            // receiving data failed => remote side rejected one of our packets
            // due to the nature of UDP, there's no way to tell which one exactly
            // $peer is not filled either

            throw new Exception('Invalid message');
        }

        $peerAddress = $this->sanitizeAddress($peerAddress);

        return $data;
    }

    protected function handleClose()
    {
        fclose($this->socket);
    }
}
