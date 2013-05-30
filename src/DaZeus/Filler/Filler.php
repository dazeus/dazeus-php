<?php

namespace DaZeus\Filler;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use StdClass;

class Filler implements FillerInterface
{
    protected $loop;

    protected $logger;

    public function __construct(LoopInterface $loop, LoggerInterface $logger = null)
    {
        $this->loop = $loop;

        if ($logger === null) {
            $logger = new NullLogger();
        }
        $this->logger = $logger;
    }

    public function fill($promise)
    {
        if ($promise instanceof PromiseInterface) {
            $obj = new StdClass();
            $obj->filled = false;
            $obj->result = null;
            $logger = $this->logger;
            $promise->then(function ($result) use ($obj) {
                $obj->filled = true;
                $obj->result = $result;
                return $result;
            }, function () {
                throw new CouldNotFulfillException();
            });

            $loops = 0;
            while ($obj->filled === false) {
                $this->loop->tick();
                $loops += 1;
            }
            return $obj->result;
        } else {
            return $promise;
        }
    }
}
