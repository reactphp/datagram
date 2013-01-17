<?php

class DatagramServer extends DatagramSocketReadable
{
    public static function create(LoopInterface $loop, $port, $host)
    {
        $address = self::createAddress($host, $port);
        
        $socket = stream_socket_server("udp://" . $address, $errno, $errstr, STREAM_SERVER_BIND);
        if (!$socket) {
            die("$errstr ($errno)");
        }
        return new DatagramServer($loop, $socket, $address);
    }
    public function __construct(LoopInterface $loop, $socket, $address = null)
    {
        if ($address === null) {
            $address = stream_socket_get_name($socket, false);
        }
        parent::__construct($loop, $socket, $address);
        $this->resume();
    }
    
    public function broadcast($message, $port)
    {
        // TODO: no way to set SO_BROADCAST option?
        $address = '255.255.255.255:' . $port;
        stream_socket_sendto($this->socket, $message, 0, $address);
    }
}
