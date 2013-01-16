<?php

class DatagramClient extends DatagramSocket
{
    public static function factory($host, $port)
    {
        // todo: resolve host via react/dns => promise
        $address = $host . ':' . $port;
        $socket = stream_socket_client('udp://' . $address, $errno, $errstr)
        
        
        return new DatagramClient($socket, $address);
    }
    
    public function __construct($socket, $address)
    {
        parent::__construct($socket, $address);
        $loop->addReadStream($socket, array('this', 'onReceive'));
    }
}