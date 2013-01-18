<?php

namespace Datagram;

/** @event message */
// interface similar to Stream
// TODO: implment EventEmitter only here?
class SocketReadable extends Socket
{
    public $bufferSize = 65536;

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

        // create remote socket that does NOT have a dedicated readable method (see thie main DatagramSocket instead)
        $remote = new Socket($this->loop, $this->socket, $peer);

        $this->emit('message', array($data, $remote));
    }

    public function close()
    {
        $this->pause();
        fclose($this->socket);
        $this->socket = false;
    }
}
