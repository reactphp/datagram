# Changelog

## 1.9.0 (2022-12-05)

*   Feature: Add support for PHP 8.1 and PHP 8.2.
    (#44 by @SimonFrings and #51 by @WyriHaximus)

*   Feature: Forward compatibility with upcoming Promise v3.
    (#33 by @WyriHaximus)

*   Feature / Fix: Improve error reporting when custom error handler is used.
    (#49 by @clue)

*   Feature: Avoid dependency on `ext-filter`.
    (#45 by @clue)

*   Improve documentation and examples and update to use new reactphp/async package.
    (#50 by @nhedger, #53 by @dinooo13 and #47, #54 and #56 by @SimonFrings)

*   Improve test suite and report failed assertions.
    (#48 by @SimonFrings and #55 by @clue)

## 1.8.0 (2021-07-11)

A major new feature release, see [**release announcement**](https://clue.engineering/2021/announcing-reactphp-default-loop).

*   Feature: Simplify usage by supporting new [default loop](https://reactphp.org/event-loop/#loop).
    (#42 by @clue)

    ```php
    // old (still supported)
    $factory = new React\Datagram\Factory($loop);

    // new (using default loop)
    $factory = new React\Datagram\Factory();
    ```

## 1.7.0 (2021-06-25)

*   Feature: Support falling back to multiple DNS servers from DNS config.
    (#41 by @clue)

    When using the `Factory`, it will now use all DNS servers configured on your
    system. If you have multiple DNS servers configured and connectivity to the
    primary DNS server is broken, it will now fall back to your other DNS
    servers, thus providing improved connectivity and redundancy for broken DNS
    configurations.

## 1.6.0 (2021-02-12)

*   Feature: Support PHP 8 (socket address of closed socket should be null).
    (#39 by @clue)

*   Improve test suite and add `.gitattributes` to exclude dev files from exports.
    Run tests on PHPUnit 9, switch to GitHub actions and clean up test suite.
    (#30, #31 and #38 by @clue, #34 by @reedy, #35 by @WyriHaximus and #37 by @SimonFrings)

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
