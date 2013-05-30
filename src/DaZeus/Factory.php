<?php

namespace DaZeus;

use DaZeus\Filler\FillerLoop;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use React\EventLoop\LoopInterface;
use React\EventLoop\Factory as EventLoopFactory;


class Factory
{
    public static function create($socket, LoopInterface $loop = null, LoggerInterface $logger = null)
    {
        if ($loop === null) {
            $loop = EventLoopFactory::create();
        }

        if ($logger === null) {
            $logger = new NullLogger();
        }

        $loop = new FillerLoop($loop, $logger);
        $connection = new Connection($socket, $loop, $logger);
        return new DaZeusAsync($connection, $logger);
    }

    public static function plugin($socket, $pluginClass, LoopInterface $loop = null, LoggerInterface $logger = null)
    {
        $dazeus = self::create($socket, $loop, $logger);
        return new $pluginClass($dazeus, $logger);
    }
}
