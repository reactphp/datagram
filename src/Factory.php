<?php

namespace React\Datagram;

use React\Datagram\Socket;
use React\Dns\Config\Config;
use React\Dns\Resolver\Factory as DnsFactory;
use React\Dns\Resolver\Resolver;
use React\EventLoop\LoopInterface;
use React\Promise;
use React\Promise\CancellablePromiseInterface;
use \Exception;

class Factory
{
    protected $loop;
    protected $resolver;

    /**
     *
     * @param LoopInterface $loop
     * @param Resolver|null $resolver Resolver instance to use. Will otherwise
     *     try to load the system default DNS config or fall back to using
     *     Google's public DNS 8.8.8.8
     */
    public function __construct(LoopInterface $loop, Resolver $resolver = null)
    {
        if ($resolver === null) {
            // try to load nameservers from system config or default to Google's public DNS
            $config = Config::loadSystemConfigBlocking();
            $server = $config->nameservers ? \reset($config->nameservers) : '8.8.8.8';

            $factory = new DnsFactory();
            $resolver = $factory->create($server, $loop);
        }

        $this->loop = $loop;
        $this->resolver = $resolver;
    }

    public function createClient($address)
    {
        $loop = $this->loop;

        return $this->resolveAddress($address)->then(function ($address) use ($loop) {
            $socket = @\stream_socket_client($address, $errno, $errstr);
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
            $socket = @\stream_socket_server($address, $errno, $errstr, \STREAM_SERVER_BIND);
            if (!$socket) {
                throw new Exception('Unable to create server socket: ' . $errstr, $errno);
            }

            return new Socket($loop, $socket);
        });
    }

    protected function resolveAddress($address)
    {
        if (\strpos($address, '://') === false) {
            $address = 'udp://' . $address;
        }

        // parse_url() does not accept null ports (random port assignment) => manually remove
        $nullport = false;
        if (\substr($address, -2) === ':0') {
            $address = \substr($address, 0, -2);
            $nullport = true;
        }

        $parts = \parse_url($address);

        if (!$parts || !isset($parts['host'])) {
            return Promise\resolve($address);
        }

        if ($nullport) {
            $parts['port'] = 0;
        }

        // remove square brackets for IPv6 addresses
        $host = \trim($parts['host'], '[]');

        return $this->resolveHost($host)->then(function ($host) use ($parts) {
            $address = $parts['scheme'] . '://';

            if (isset($parts['port']) && \strpos($host, ':') !== false) {
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
        if (false !== \filter_var($host, \FILTER_VALIDATE_IP)) {
            return Promise\resolve($host);
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
