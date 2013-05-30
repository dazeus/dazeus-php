<?php

namespace DaZeus;

use DaZeus\Filler\FillerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class Plugin implements FillerInterface
{
    protected $dazeus;

    protected $logger;

    public function __construct(DaZeusInterface $dazeus, LoggerInterface $logger = null)
    {
        if ($logger === null) {
            $logger = new NullLogger();
        }
        $this->logger = $logger;

        $this->dazeus = $dazeus;
        $this->dazeus->doHandshake($this->getName(), $this->getVersion(), $this->getConfig());
        foreach ($this->registerEvents() as $event => $action) {
            if (!is_string($event)) {
                $event = $action;
                $action = [$this, 'on' . ucfirst(strtolower($event))];
            }
            $this->dazeus->subscribe($event, $action);
        }

        foreach ($this->registerCommands() as $command => $action) {
            if (!is_string($command)) {
                $command = $action;
                $action = [$this, 'on' . ucfirst($command) . 'Command'];
            }
            $this->dazeus->onCommand($command, $action);
        }
        $this->onInit();
    }

    public static function classname()
    {
        return get_called_class();
    }

    protected function registerEvents()
    {
        return [];
    }

    protected function registerCommands()
    {
        return [];
    }

    protected function onInit()
    {
        // default: do nothing
    }

    abstract public function getName();
    abstract public function getVersion();

    public function getConfig()
    {
        return $this->getName();
    }

    public function run()
    {
        $this->dazeus->run();
    }

    public function getLoop()
    {
        return $this->dazeus->getLoop();
    }

    public function fill($promise)
    {
        return $this->dazeus->fill($promise);
    }

    public function getDaZeus()
    {
        return $this->dazeus;
    }
}
