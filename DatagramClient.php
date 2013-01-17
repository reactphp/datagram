<?php

class DatagramClient extends DatagramSocket
{
    public static function factory($host, $port)
    {
        // todo: resolve host via react/dns => promise
        $address = self::createAddress($host, $port);
        $socket = stream_socket_client('udp://' . $address, $errno, $errstr)
        
        return new DatagramClient($socket, $address);
    }
    
    public function __construct($socket, $address = null)
    {
        if ($address === null) {
            $address = stream_socket_get_peer($socket, false);
        }
        parent::__construct($socket, $address);
        $this->resume();
    }
}
