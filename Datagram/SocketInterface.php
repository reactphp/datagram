<?php

namespace Datagram;

use Evenement\EventEmitterInterface;

/**
 * @event message($data, $remoteAddress, $thisSocket)
 * @event error($exception, $thisSocket)
 * @event close($thisSocket)
 */
interface SocketInterface extends EventEmitterInterface
{
    public function send($data, $remoteAddress = null);

    public function close();

    public function resume();

    public function pause();
}
