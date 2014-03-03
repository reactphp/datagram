# CHANGELOG

This file is a manually maintained list of changes for each release. Feel free
to add your changes here when sending pull requests. Also send corrections if
you spot any mistakes.

## 0.4.0 (2014-03-03)

* BC break: Unified socket addresses (string URIs instead of host+port)
  ([#5](https://github.com/clue/datagram/pull/5)):
  
  * The `Factory` now only accepts a single-argument full socket address (i.e. host:port for UDP/IP):

    ```php
// Previously:
$factory->createServer(1337, 'localhost')->then(…);
$factory->createClient('localhost', 1337)->then(…);
// Now:
$factory->createServer('localhost:1337')->then(…);
$factory->createClient('localhost:1337')->then(…);
```

  * The methods `Socket::getAddress()` and `Socket::getPort()` have been removed.
  * Instead, the following two methods have been introduced which both return
    a full socket address:
    * `SocketInterface::getLocalAddress()`
    * `SocketInterface::getRemoteAddress()`
* Small refactoring to ease extending base classes
  ([#4](https://github.com/clue/datagram/pull/4))

## 0.3.0 (2013-06-23)

* Feature: Add `Datagram\Socket::end()` method which closes the socket as soon
as the remaining outgoing buffer has been sent.
* Fix: Actually close underlying socket descriptor in `Datagram\Socket::close()`.

## 0.2.0 (2013-04-16)

* BC break: Whole new API, adapted to nodejs's Socket.dgram API.
* BC break: Unified `Datagram\Socket` instead of `Datagram\Client` and `Datagram\Server`
* Support react v0.3

## 0.1.0 (2013-01-21)

* First tagged release
