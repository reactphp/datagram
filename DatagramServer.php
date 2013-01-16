<?php

class DatagramServer
{
    public function __construct(LoopInterface $loop, $port, $host='127.0.0.1')
    {
        $this->socket = stream_socket_server("udp://".$host.":".$port, $errno, $errstr, STREAM_SERVER_BIND);
        if (!$socket) {
            die("$errstr ($errno)");
        }
        
        $size = 1500;
        $that = $this;
        $loop->addReadStream($this->socket, function($socket) use ($that, $size) {
            $data = stream_socket_recvfrom($socket, $size, 0, $peer);
            $socket = new DatagramSocket($that, $peer);
            
            $that->emit('message', array($data, $socket);
        });
    }
}
