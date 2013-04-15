<?php

namespace Datagram;

use Evenement\EventEmitterInterface;

/**
 * @event message($data, $remoteAddress)
 * @event error($exception)
 */
interface SocketInterface extends EventEmitterInterface
{
    public function send($data, $remoteAddress = null);

    public function close();

    public function resume();

    public function pause();
}
