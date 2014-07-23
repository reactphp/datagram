<?php

require_once __DIR__.'/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$factory = new Datagram\Factory($loop);

$factory->createServer('localhost:1234')->then(function (Datagram\Socket $server) {
    $server->on('message', function($message, $address, $server) {
        $server->send('hello ' . $address . '! echo: ' . $message, $address);

        echo 'client ' . $address . ': ' . $message . PHP_EOL;
    });
});

$loop->run();
