<?php

namespace React\Datagram;

use React\EventLoop\LoopInterface;
use React\Dns\Resolver\Resolver;
use React\Promise;
use React\Datagram\Socket;
use \Exception;
use React\Promise\CancellablePromiseInterface;

class Factory
{
    protected $loop;
    protected $resolver;

    public function __construct(LoopInterface $loop, Resolver $resolver = null)
    {
        $this->loop = $loop;
        $this->resolver = $resolver;
    }

    public function createClient($address)
    {
        $loop = $this->loop;

        return $this->resolveAddress($address)->then(function ($address) use ($loop) {
            $socket = @stream_socket_client($address, $errno, $errstr);
            if (!$socket) {
                throw new Exception('Unable to create client socket: ' . $errstr, $errno);
            }

            return new Socket($loop, $socket);
        });
    }

    public function createServer($address)
    {
        $loop = $this->loop;

        return $this->resolveAddress($address)->then(function ($address) use ($loop) {
            $socket = @stream_socket_server($address, $errno, $errstr, STREAM_SERVER_BIND);
            if (!$socket) {
                throw new Exception('Unable to create server socket: ' . $errstr, $errno);
            }

            return new Socket($loop, $socket);
        });
    }

    protected function resolveAddress($address)
    {
        if (strpos($address, '://') === false) {
            $address = 'udp://' . $address;
        }

        // parse_url() does not accept null ports (random port assignment) => manually remove
        $nullport = false;
        if (substr($address, -2) === ':0') {
            $address = substr($address, 0, -2);
            $nullport = true;
        }

        $parts = parse_url($address);

        if (!$parts || !isset($parts['host'])) {
            return Promise\resolve($address);
        }

        if ($nullport) {
            $parts['port'] = 0;
        }

        // remove square brackets for IPv6 addresses
        $host = trim($parts['host'], '[]');

        return $this->resolveHost($host)->then(function ($host) use ($parts) {
            $address = $parts['scheme'] . '://';

            if (isset($parts['port']) && strpos($host, ':') !== false) {
                // enclose IPv6 address in square brackets if a port will be appended
                $host = '[' . $host . ']';
            }

            $address .= $host;

            if (isset($parts['port'])) {
                $address .= ':' . $parts['port'];
            }

            return $address;
        });
    }

    protected function resolveHost($host)
    {
        // there's no need to resolve if the host is already given as an IP address
        if (false !== filter_var($host, FILTER_VALIDATE_IP)) {
            return Promise\resolve($host);
        }
        // todo: remove this once the dns resolver can handle the hosts file!
        if ($host === 'localhost') {
            return Promise\resolve('127.0.0.1');
        }

        if ($this->resolver === null) {
            return Promise\reject(new Exception('No resolver given in order to get IP address for given hostname'));
        }

        $promise = $this->resolver->resolve($host);

        // wrap DNS lookup in order to control cancellation behavior
        return new Promise\Promise(
            function ($resolve, $reject) use ($promise) {
                // forward promise resolution
                $promise->then($resolve, $reject);
            },
            function ($_, $reject) use ($promise) {
                // reject with custom message once cancelled
                $reject(new \RuntimeException('Cancelled creating socket during DNS lookup'));

                // (try to) cancel pending DNS lookup, otherwise ignoring its results
                if ($promise instanceof CancellablePromiseInterface) {
                    $promise->cancel();
                }
            }
        );
    }
}
