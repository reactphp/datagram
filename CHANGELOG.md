# CHANGELOG

This file is a manually maintained list of changes for each release. Feel free
to add your changes here when sending pull requests. Also send corrections if
you spot any mistakes.

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

