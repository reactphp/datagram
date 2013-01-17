<?php

class DatagramSocket extends EventEmitter implements SendInterface
{
    public $bufferSize = 1500;
    
    public function __construct($loop, $socket, $address)
    {
        $this->loop = $loop;
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
    
    public function send($data)
    {
        stream_socket_sendto($this->socket, $data, 0, $this->address);
    }
    
    public function pause()
    {
        $this->loop->removeReadStream($this->socket);
    }
    
    public function resume()
    {
        $this->loop->addReadStream($this->socket, array($this, 'onReceive'));
    }
    
    public function onReceive($message)
    {
        $data = stream_socket_recvfrom($this->socket, $this->bufferSize, 0, $peer);
        $remote = new DatagramSocket($this->socket, $peer);
            
        $this->emit('message', array($data, $socket);
    }
    
    protected static function createAddress($host, $ip)
    {
        $address = $host;
        if (strpos($host, ':') !== false) {
            // enclose IPv6 address in square brackets
            $address = '[' . $host . ']';
        }
        $address .= $port;
    }
}
