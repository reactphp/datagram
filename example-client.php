<?php

$loop = React\Loop\Factory::create();

$client = DatagramClient::factory('localhost', 1234);

$client->send('first');

$client->on('message', function($message, $server) {
    //$remote->send() is same as $client->send()
    
    echo 'received "' . $message . '" from ' . $server->getAddress() . PHP_EOL;
});

$loop->run();
