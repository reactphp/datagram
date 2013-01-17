<?php

class DatagramClient extends DatagramSocketReadable
{   
    public function __construct($loop, $socket, $address = null)
    {
        if ($address === null) {
            $address = stream_socket_get_name($socket, true);
        }
        parent::__construct($loop, $socket, $address);
        $this->resume();
    }
}
