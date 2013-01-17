<?php

// TODO: should not provide a send() method
class DatagramServer extends DatagramSocketReadable
{
    public function __construct(LoopInterface $loop, $socket, $address = null)
    {
        if ($address === null) {
            $address = stream_socket_get_name($socket, false);
        }
        parent::__construct($loop, $socket, $address);
        $this->resume();
    }
}
