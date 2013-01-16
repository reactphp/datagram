<?php

class DatagramSocket
{
    public function __construct($socket, $address)
    {
        $this->socket = $socket;
        $this->address = $address;
    }
    
    public function getAddress()
    {
        // TODO: doc comment suggests IPv6 address is not enclosed in square brackets?
        return $this->address;
    }
    
    public function getPort()
    {
        return (int)substr($this->address, strrpos($this->address, ':') + 1);
    }
    
    public function getHost(){
        return trim(substr($this->address, 0, strrpos($this->address, ':'), '[]');
    }
    
    public function write($data)
    {
        stream_socket_sendto($this->socket, $data, 0, $this->address);
    }
}
