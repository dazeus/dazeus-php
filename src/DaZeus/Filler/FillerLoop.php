<?php

namespace DaZeus\Filler;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;
use React\Promise\PromiseInterface;
use StdClass;

class FillerLoop implements FillerLoopInterface
{
    protected $loop;

    protected $logger;

    protected $isActive;

    public function __construct(LoopInterface $loop, LoggerInterface $logger = null)
    {
        $this->loop = $loop;

        if ($logger === null) {
            $logger = new NullLogger();
        }

        $this->logger = $logger;
        $this->isActive = false;
    }

    /**
     * {@inheritdoc}
     */
    public function addReadStream($stream, $listener)
    {
        return $this->loop->addReadStream($stream, $listener);
    }

    /**
     * {@inheritdoc}
     */
    public function addWriteStream($stream, $listener)
    {
        return $this->loop->addWriteStream($stream, $listener);
    }

    /**
     * {@inheritdoc}
     */
    public function removeReadStream($stream)
    {
        return $this->loop->removeReadStream($stream);
    }

    /**
     * {@inheritdoc}
     */
    public function removeWriteStream($stream)
    {
        return $this->loop->removeWriteStream($stream);
    }

    /**
     * {@inheritdoc}
     */
    public function removeStream($stream)
    {
        return $this->loop->removeStream($stream);
    }

    /**
     * {@inheritdoc}
     */
    public function addTimer($interval, $callback)
    {
        return $this->loop->addTimer($interval, $callback);
    }

    /**
     * {@inheritdoc}
     */
    public function addPeriodicTimer($interval, $callback)
    {
        return $this->loop->addPeriodicTimer($interval, $callback);
    }

    /**
     * {@inheritdoc}
     */
    public function cancelTimer(TimerInterface $timer)
    {
        return $this->loop->cancelTimer($timer);
    }

    /**
     * {@inheritdoc}
     */
    public function isTimerActive(TimerInterface $timer)
    {
        return $this->loop->isTimerActive($timer);
    }

    /**
     * {@inheritdoc}
     */
    public function tick()
    {
        return $this->loop->tick();
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->isActive = true;
        return $this->loop->run();
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
        $result = $this->loop->stop();
        $this->isActive = false;
        return $result;
    }

    /**
     * Returns whether or not the loop is running
     * @return boolean
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * {@inheritdoc}
     */
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
