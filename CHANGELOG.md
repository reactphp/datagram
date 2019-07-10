# Changelog

## 1.5.0 (2019-07-10)

*   Feature: Forward compatibility with upcoming stable DNS component.
    (#29 by @clue)

*   Prefix all global functions calls with \ to skip the look up and resolve process and go straight to the global function.
    (#28 by @WyriHaximus)

*   Improve test suite to also test against PHP 7.1 and 7.2.
    (#25 by @andreybolonin)

## 1.4.0 (2018-02-28)

*   Feature: Update DNS dependency to support loading system default DNS
    nameserver config on all supported platforms
    (`/etc/resolv.conf` on Unix/Linux/Mac/Docker/WSL and WMIC on Windows)
    (#23 by @clue)

    This means that connecting to hosts that are managed by a local DNS server,
    such as a corporate DNS server or when using Docker containers, will now
    work as expected across all platforms with no changes required:

    ```php
    $factory = new Factory($loop);
    $factory->createClient('intranet.example:5353');
    ```

*   Improve README
    (#22 by @jsor)

## 1.3.0 (2017-09-25)

*   Feature: Always use `Resolver` with default DNS to match Socket component
    and update DNS dependency to support hosts file on all platforms
    (#19 and #20 by @clue)

    This means that connecting to hosts such as `localhost` (and for example
    those used for Docker containers) will now work as expected across all
    platforms with no changes required:

    ```php
    $factory = new Factory($loop);
    $factory->createClient('localhost:5353');
    ```

## 1.2.0 (2017-08-09)

* Feature: Target evenement 3.0 a long side 2.0 and 1.0
  (#16 by @WyriHaximus)

* Feature: Forward compatibility with EventLoop v1.0 and v0.5
  (#18 by @clue)

* Improve test suite by updating Travis build config so new defaults do not break the build
  (#17 by @clue)

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
