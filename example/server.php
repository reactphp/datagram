<?php

require_once __DIR__.'/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$factory = new Datagram\Factory();

$factory->createServer($loop, 1234)->then(function (Datagram\Server $server) {
    $server->on('message', function($message, $client) {
        $client->send('hello '.$client->getAddress().'! echo: '.$message);

        // $server->send() is not available here
    });
});

$loop->run();
