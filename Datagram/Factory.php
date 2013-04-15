<?php

namespace Datagram;

use React\EventLoop\LoopInterface;
use React\Dns\Resolver\Resolver;
use React\Promise\When;
use Datagram\Socket;
use \Exception;

class Factory
{
    protected $loop;
    protected $resolver;

    public function __construct(LoopInterface $loop, Resolver $resolver = null)
    {
        $this->loop = $loop;
        $this->resolver = $resolver;
    }

    public function createClient($host, $port)
    {
        $factory = $this;
        $loop = $this->loop;
        return $this->resolve($host)->then(function ($ip) use ($loop, $port, $factory) {
            $address = $factory->createAddress($ip, $port);
            $socket = stream_socket_client('udp://' . $address, $errno, $errstr);
            if (!$socket) {
                throw new Exception('Unable to create client socket: ' . $errstr, $errno);
            }

            return new Socket($loop, $socket, $address);
        });
    }

    public function createServer($port, $host = '127.0.0.1')
    {
        $address = $this->createAddress($host, $port);

        $socket = stream_socket_server("udp://" . $address, $errno, $errstr, STREAM_SERVER_BIND);
        if (!$socket) {
            return When::reject(new Exception('Unable to create server socket: ' . $errstr, $errno));
        }

        return When::resolve(new Socket($this->loop, $socket, $address));
    }

    protected function resolve($host)
    {
        // there's no need to resolve if the host is already given as an IP address
        if (false !== filter_var($host, FILTER_VALIDATE_IP)) {
            return When::resolve($host);
        }
        // todo: remove this once the dns resolver can handle the hosts file!
        if ($host === 'localhost') {
            return When::resolve('127.0.0.1');
        }

        if ($this->resolver === null) {
            return When::reject(\Exception('No resolver given in order to get IP address for given hostname'));
        }
        return $this->resolver->resolve($host);
    }

    public function createAddress($host, $port)
    {
        if (strpos($host, ':') !== false) {
            // enclose IPv6 address in square brackets
            $host = '[' . $host . ']';
        }
        return $host . ':' . $port;
    }
}
