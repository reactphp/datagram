<?php

require_once __DIR__.'/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$factory = new Datagram\Factory($loop);

$factory->createServer(1234)->then(function (Datagram\Server $server) {
    $server->on('message', function($message, $client) {
        $client->send('hello '.$client->getAddress().'! echo: '.$message);

        echo 'client ' . $client->getAddress() . ': ' . $message . PHP_EOL;
    });
});

$loop->run();
