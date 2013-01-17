<?php

class DatagramFactory
{
    public function createClient(LoopInterface $loop, Resolver $resolver, $host, $port)
    {
        return $resolver->resolve($host)->then(function ($ip) use ($loop, $port) {
            $address = self::createAddress($ip, $port);
            $socket = stream_socket_client('udp://' . $address, $errno, $errstr)

            return new DatagramClient($loop, $socket, $address);
        });
    }

    public function createServer(LoopInterface $loop, $port, $host = '127.0.0.1')
    {
        $address = self::createAddress($host, $port);
        
        $socket = stream_socket_server("udp://" . $address, $errno, $errstr, STREAM_SERVER_BIND);
        if (!$socket) {
            die("$errstr ($errno)");
        }
        return When::resolve(new DatagramServer($loop, $socket, $address));
    }
}
