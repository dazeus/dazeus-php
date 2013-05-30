<?php

namespace DaZeus\Filler;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;

class FillerLoop implements FillerLoopInterface
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
        $this->filler = new Filler($loop, $logger);
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
        return $this->loop->run();
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
        return $this->loop->stop();
    }

    /**
     * {@inheritdoc}
     */
    public function fill($promise)
    {
        return $this->filler->fill($promise);
    }
}
