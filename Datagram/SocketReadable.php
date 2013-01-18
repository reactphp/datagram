<?php

namespace Datagram;

/** @event message */
// interface similar to Stream
// TODO: implment EventEmitter only here?
class SocketReadable extends Socket
{
    public $bufferSize = 1500;

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

        // create remote socket that does NOT have a dedicated readable method (see thie main DatagramSocket instead)
        $remote = new DatagramSocket($this->socket, $peer);

        $this->emit('message', array($data, $socket));
    }
}
