<?php

require_once __DIR__.'/../vendor/autoload.php';

$factory = new React\Datagram\Factory();

$factory->createServer('localhost:1234')->then(function (React\Datagram\Socket $server) {
    $server->on('message', function($message, $address, $server) {
        $server->send('hello ' . $address . '! echo: ' . $message, $address);

        echo 'client ' . $address . ': ' . $message . PHP_EOL;
    });
});
