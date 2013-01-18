<?php

class DatagramFactory
{
    public function createClient(LoopInterface $loop, Resolver $resolver, $host, $port)
    {
        $factory = $this;
        return $this->resolve($resolver, $host)->then(function ($ip) use ($loop, $port, $factory) {
            $address = $factory->createAddress($ip, $port);
            $socket = stream_socket_client('udp://' . $address, $errno, $errstr);

            return new DatagramClient($loop, $socket, $address);
        });
    }

    public function createServer(LoopInterface $loop, $port, $host = '127.0.0.1')
    {
        $address = $this->createAddress($host, $port);

        $socket = stream_socket_server("udp://" . $address, $errno, $errstr, STREAM_SERVER_BIND);
        if (!$socket) {
            die("$errstr ($errno)");
        }
        return When::resolve(new DatagramServer($loop, $socket, $address));
    }

    protected function resolve($resolver, $host)
    {
        // todo: if there's no need to resolve the address:
        if (false) {
            return When::resolve($host);
        }
        return $resolver->resolve($host);
    }

    public function createAddress($host, $port)
    {
        $address = $host;
        if (strpos($host, ':') !== false) {
            // enclose IPv6 address in square brackets
            $address = '[' . $host . ']';
        }
        $address .= $port;
        return $address;
    }
}
