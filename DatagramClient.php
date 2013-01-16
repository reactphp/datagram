<?php

class DatagramClient
{
    public function connect($host, $port)
    {
        // todo: resolve host via react/dns => promise
        $address = $host . ':' . $port;
        $socket = stream_socket_client('udp://' . $address, $errno, $errstr)
        return new DatagramSocket($socket, $address);
    }
}