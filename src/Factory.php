<?php

namespace React\Datagram;

use React\Datagram\Socket;
use React\Dns\Config\Config as DnsConfig;
use React\Dns\Resolver\Factory as DnsFactory;
use React\Dns\Resolver\ResolverInterface;
use React\EventLoop\Loop;
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
     * This class takes an optional `LoopInterface|null $loop` parameter that can be used to
     * pass the event loop instance to use for this object. You can use a `null` value
     * here in order to use the [default loop](https://github.com/reactphp/event-loop#loop).
     * This value SHOULD NOT be given unless you're sure you want to explicitly use a
     * given event loop instance.
     *
     * @param ?LoopInterface $loop
     * @param ?ResolverInterface $resolver Resolver instance to use. Will otherwise
     *     try to load the system default DNS config or fall back to using
     *     Google's public DNS 8.8.8.8
     */
    public function __construct(LoopInterface $loop = null, ResolverInterface $resolver = null)
    {
        $loop = $loop ?: Loop::get();
        if ($resolver === null) {
            // try to load nameservers from system config or default to Google's public DNS
            $config = DnsConfig::loadSystemConfigBlocking();
            if (!$config->nameservers) {
                $config->nameservers[] = '8.8.8.8'; // @codeCoverageIgnore
            }

            $factory = new DnsFactory();
            $resolver = $factory->create($config, $loop);
        }

        $this->loop = $loop;
        $this->resolver = $resolver;
    }

    public function createClient($address, array $socketOptions = array())
    {
        $loop = $this->loop;
        $that = $this;

        return $this->resolveAddress($address)->then(function ($address) use ($loop, $socketOptions, $that) {
            $socket = @\stream_socket_client($address, $errno, $errstr);
            if (!$socket) {
                throw new Exception('Unable to create client socket: ' . $errstr, $errno);
            }
            $that->assignSocketOptionsToStream($socket, $socketOptions);

            return new Socket($loop, $socket);
        });
    }

    public function createServer($address, array $socketOptions = array())
    {
        $loop = $this->loop;
        $that = $this;

        return $this->resolveAddress($address)->then(function ($address) use ($loop, $socketOptions, $that) {
            $socket = @\stream_socket_server($address, $errno, $errstr, \STREAM_SERVER_BIND);
            if (!$socket) {
                throw new Exception('Unable to create server socket: ' . $errstr, $errno);
            }
            $that->assignSocketOptionsToStream($socket, $socketOptions);

            return new Socket($loop, $socket);
        });
    }

    /**
     * Allows to set custom socket options on a stream resource. This is useful for
     * setting options like `SO_REUSEADDR` or `SO_BROADCAST`. This method will
     * throw an exception if the stream resource is not a socket resource.
     * Example:
     * ```php
     * $factory->assignSocketOptionsToStream($socket, array(
     *   SOL_SOCKET => array( // SOL_SOCKET is the level
     *     SO_REUSEADDR => 1,
     *     SO_BROADCAST' => 1,
     *     SO_BINDTODEVICE => 'eth0',
     *  ),
     * ));
     * ```
     * @param $stream
     * @param $socketOptions
     * @return void
     * @throws Exception
     */
    protected function assignSocketOptionsToStream($stream, $socketOptions) {
        if(
            $stream &&
            $socketOptions &&
            function_exists('socket_import_stream') &&
            function_exists('socket_set_option'))
        {
            $socket = @socket_import_stream($stream);
            foreach($socketOptions as $level => $options) {
                foreach($options as $option => $value) {
                    if(!socket_set_option($socket, $level, $option, $value)) {
                        throw new Exception('Unable to set socket option: ' . socket_strerror(socket_last_error($socket)));
                    }
                }
            }
        }
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
        if (@\inet_pton($host) !== false) {
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
