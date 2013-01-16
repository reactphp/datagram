<?php

$loop = React\Loop\Factory::create();

$server = new DatagramServer($loop, 1234);
$server->on('message', function($data, $client) {
    $client->send('hello '.$client->getAddress().'! echo: '.$message);
    
    // $server->send() is not available here
});

$loop->run();
