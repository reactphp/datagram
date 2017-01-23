# Changelog

## 1.1.1 (2017-01-23)

* Fix: Properly format IPv6 addresses and return `null` for unknown addresses
  (#14 by @clue)

* Fix: Skip IPv6 tests if not supported by the system
  (#15 by @clue)

## 1.1.0 (2016-03-19)

* Feature: Support promise cancellation (cancellation of underlying DNS lookup)
  (#12 by @clue)

* Fix: Fix error reporting when trying to create invalid sockets
  (#11 by @clue)

* Improve test suite and update dependencies
  (#7, #8 by @clue)

## 1.0.1 (2015-11-13)

* Fix: Correct formatting for remote peer address of incoming datagrams when using IPv6
  (#6 by @WyriHaximus)

* Improve test suite for different PHP versions

## 1.0.0 (2014-10-23)

* Initial tagged release

> This project has been migrated over from [clue/datagram](https://github.com/clue/php-datagram)
> which has originally been released in January 2013.
> Upgrading from clue/datagram v0.5.0? Use namespace `React\Datagram` instead of `Datagram` and you're ready to go!
