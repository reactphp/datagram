<?php

namespace Datagram;

use React\EventLoop\LoopInterface;
use React\Dns\Resolver\Resolver;
use React\Promise\When;

class Factory
{
    public function createClient(LoopInterface $loop, Resolver $resolver, $host, $port)
    {
        $factory = $this;
        return $this->resolve($resolver, $host)->then(function ($ip) use ($loop, $port, $factory) {
            $address = $factory->createAddress($ip, $port);
            $socket = stream_socket_client('udp://' . $address, $errno, $errstr);
            if (!$socket) {
                die("$errstr ($errno)");
            }


            return new Client($loop, $socket, $address);
        });
    }

    public function createServer(LoopInterface $loop, $port, $host = '127.0.0.1')
    {
        $address = $this->createAddress($host, $port);

        $socket = stream_socket_server("udp://" . $address, $errno, $errstr, STREAM_SERVER_BIND);
        if (!$socket) {
            die("$errstr ($errno)");
        }
        return When::resolve(new Server($loop, $socket, $address));
    }

    protected function resolve($resolver, $host)
    {
        // there's no need to resolve if the host is already given as an IP address
        if (false !== filter_var($host, FILTER_VALIDATE_IP)) {
            return When::resolve($host);
        }
        // todo: remove this once the dns resolver can handle the hosts file!
        if ($host === 'localhost') {
            return When::resolve('127.0.0.1');
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
        $address .= ':' . $port;
        return $address;
    }
}
