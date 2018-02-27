<?php

require_once __DIR__.'/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$factory = new React\Datagram\Factory($loop);

$factory->createClient('localhost:1234')->then(function (React\Datagram\Socket $client) use ($loop) {
    $client->send('first');

    $client->on('message', function($message, $serverAddress, $client) {
        echo 'received "' . $message . '" from ' . $serverAddress. PHP_EOL;
    });

    $client->on('error', function($error, $client) {
        echo 'error: ' . $error->getMessage() . PHP_EOL;
    });

    $n = 0;
    $tid = $loop->addPeriodicTimer(2.0, function() use ($client, &$n) {
        $client->send('tick' . ++$n);
    });

    // read input from STDIN and forward everything to server
    $loop->addReadStream(STDIN, function () use ($client, $loop, $tid) {
        $msg = fgets(STDIN, 2000);
        if ($msg === false) {
            // EOF => flush client and stop perodic sending and waiting for input
            $client->end();
            $loop->cancelTimer($tid);
            $loop->removeReadStream(STDIN);
        } else {
            $client->send(trim($msg));
        }
    });
}, function($error) {
    echo 'ERROR: ' . $error->getMessage() . PHP_EOL;
});

$loop->run();
