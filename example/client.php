<?php

$loop = React\Loop\Factory::create();

$factory = Resolver\Factory();
$resolver = $factory->createCached($loop, '8.8.8.8');

$factory = new DatagramFactory();

$factory->createClient($resolver, 'localhost', 1234)->then(function ($client) use ($loop) {
    $client->send('first');

    $client->on('message', function($message, $server) {
        //$remote->send() is same as $client->send()

        echo 'received "' . $message . '" from ' . $server->getAddress() . PHP_EOL;
    });

    $n = 0;
    $loop->addPeriodicTimer(2.0, function() use ($client, &$n) {
        $client->send('tick' . $n);
    });
});

$loop->run();
