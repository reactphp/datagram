<?php

class DatagramClient extends DatagramSocket
{
    public static function create(LoopInterface $loop, Resolver $resolver, $host, $port)
    {
        return $resolver->resolve($host)->then(function ($ip) use ($loop, $port) {
            $address = self::createAddress($ip, $port);
            $socket = stream_socket_client('udp://' . $address, $errno, $errstr)

            return new DatagramClient($loop, $socket, $address);
        });
    }
    
    public function __construct($socket, $address = null)
    {
        if ($address === null) {
            $address = stream_socket_get_name($socket, true);
        }
        parent::__construct($socket, $address);
        $this->resume();
    }
}
