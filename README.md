# Datagram

[![Build Status](https://travis-ci.org/reactphp/datagram.svg?branch=master)](https://travis-ci.org/reactphp/datagram)

Event-driven UDP datagram socket client and server for [ReactPHP](https://reactphp.org).

## Quickstart example

Once [installed](#install), you can use the following code to connect to an UDP server listening on
`localhost:1234` and send and receive UDP datagrams:  

```php
$loop = React\EventLoop\Factory::create();
$factory = new React\Datagram\Factory($loop);

$factory->createClient('localhost:1234')->then(function (React\Datagram\Socket $client) {
    $client->send('first');

    $client->on('message', function($message, $serverAddress, $client) {
        echo 'received "' . $message . '" from ' . $serverAddress. PHP_EOL;
    });
});

$loop->run();
```

See also the [examples](examples).

## Usage

This library's API is modelled after node.js's API for
[UDP / Datagram Sockets (dgram.Socket)](https://nodejs.org/api/dgram.html).

## Install

The recommended way to install this library is [through Composer](https://getcomposer.org).
[New to Composer?](https://getcomposer.org/doc/00-intro.md)

This project follows [SemVer](https://semver.org/).
This will install the latest supported version:

```bash
$ composer require react/datagram:^1.5
```

See also the [CHANGELOG](CHANGELOG.md) for details about version upgrades.

This project aims to run on any platform and thus does not require any PHP
extensions and supports running on legacy PHP 5.3 through current PHP 7+ and
HHVM.
It's *highly recommended to use PHP 7+* for this project.

## Tests

To run the test suite, you first need to clone this repo and then install all
dependencies [through Composer](https://getcomposer.org):

```bash
$ composer install
```

To run the test suite, go to the project root and run:

```bash
$ php vendor/bin/phpunit
```

## License

MIT, see [LICENSE file](LICENSE).
