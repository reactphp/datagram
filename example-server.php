<?php

$loop = React\Loop\Factory::create();

$factory = new DatagramFactory();

$factory->createServer($loop, 1234)->then(function ($server) {
    $server->on('message', function($data, $client) {
        $client->send('hello '.$client->getAddress().'! echo: '.$message);

        // $server->send() is not available here
    });
});

$loop->run();
