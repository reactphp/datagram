<?php

$loop = React\Loop\Factory::create();

$factory = new Datagram\Factory();

$factory->createServer($loop, 1234)->then(function (Datagram\Server $server) {
    $server->on('message', function($data, $client) {
        $client->send('hello '.$client->getAddress().'! echo: '.$message);

        // $server->send() is not available here
    });
});

$loop->run();
