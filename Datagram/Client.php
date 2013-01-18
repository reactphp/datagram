<?php

namespace Datagram;

use React\EventLoop\LoopInterface;

class Client extends SocketReadable
{
    public function __construct(LoopInterface $loop, $socket, $address = null)
    {
        if ($address === null) {
            $address = stream_socket_get_name($socket, true);
        }
        parent::__construct($loop, $socket, $address);
        $this->resume();
    }
}
