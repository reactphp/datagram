<?php

use React\EventLoop\Loop;

require_once __DIR__.'/../vendor/autoload.php';

$factory = new React\Datagram\Factory();

$factory->createClient('localhost:1234')->then(function (React\Datagram\Socket $client) {
    $client->send('first');

    $client->on('message', function($message, $serverAddress, $client) {
        echo 'received "' . $message . '" from ' . $serverAddress. PHP_EOL;
    });

    $client->on('error', function($error, $client) {
        echo 'error: ' . $error->getMessage() . PHP_EOL;
    });

    $n = 0;
    $tid = Loop::addPeriodicTimer(2.0, function() use ($client, &$n) {
        $client->send('tick' . ++$n);
    });

    // read input from STDIN and forward everything to server
    Loop::addReadStream(STDIN, function () use ($client, $tid) {
        $msg = fgets(STDIN, 2000);
        if ($msg === false) {
            // EOF => flush client and stop perodic sending and waiting for input
            $client->end();
            Loop::cancelTimer($tid);
            Loop::removeReadStream(STDIN);
        } else {
            $client->send(trim($msg));
        }
    });
}, function($error) {
    echo 'ERROR: ' . $error->getMessage() . PHP_EOL;
});
