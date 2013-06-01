<?php

namespace DaZeus;

use DaZeus\Filler\FillerInterface;
use Psr\Log\LoggerInterface;

class DaZeusSync implements DaZeusInterface, FillerInterface
{
    protected $loop;

    protected $async;

    public function __construct(DaZeusAsync $async)
    {
        $this->async = $async;
        $this->loop = $async->getLoop();
    }

    /**
     * {@inheritdoc}
     */
    public function networks()
    {
        return $this->loop->fill($this->async->networks());
    }

    /**
     * {@inheritdoc}
     */
    public function channels($network)
    {
        return $this->loop->fill($this->async->channels($network));
    }

    /**
     * {@inheritdoc}
     */
    public function message($network, $channel, $message)
    {
        return $this->loop->fill($this->async->message($network, $channel, $message));
    }

    /**
     * {@inheritdoc}
     */
    public function action($network, $channel, $message)
    {
        return $this->loop->fill($this->async->action($network, $channel, $message));
    }

    public function reply($network, $channel, $nick, $message, $highlight = true, $action = false)
    {
        return $this->loop->fill($this->async->reply($network, $channel, $nick, $message, $highlight, $action));
    }

    /**
     * {@inheritdoc}
     */
    public function sendNames($network, $channel)
    {
        return $this->loop->fill($this->async->sendNames($network, $channel));
    }

    /**
     * {@inheritdoc}
     */
    public function names($network, $channel)
    {
        return $this->loop->fill($this->async->names($network, $channel));
    }

    /**
     * {@inheritdoc}
     */
    public function sendWhois($network, $nick)
    {
        return $this->loop->fill($this->async->sendWhois($network, $nick));
    }

    /**
     * {@inheritdoc}
     */
    public function whois($network, $nick)
    {
        return $this->loop->fill($this->async->whois($network, $nick));
    }

    /**
     * {@inheritdoc}
     */
    public function join($network, $channel)
    {
        return $this->loop->fill($this->async->join($network, $channel));
    }

    /**
     * {@inheritdoc}
     */
    public function part($network, $channel)
    {
        return $this->loop->fill($this->async->part($network, $channel));
    }

    /**
     * {@inheritdoc}
     */
    public function nick($network)
    {
        return $this->loop->fill($this->async->nick($network));
    }

    /**
     * {@inheritdoc}
     */
    public function doHandshake($name, $version, $config)
    {
        return $this->loop->fill($this->async->doHandshake($name, $version, $config));
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig($name, $group = self::GROUP_PLUGIN)
    {
        return $this->loop->fill($this->async->getConfig($name, $group));
    }

    /**
     * {@inheritdoc}
     */
    public function highlightCharacter()
    {
        return $this->loop->fill($this->async->highlightCharacter());
    }

    /**
     * {@inheritdoc}
     */
    public function getProperty($name, array $scope = [])
    {
        return $this->loop->fill($this->async->getProperty($name, $scope));
    }

    /**
     * {@inheritdoc}
     */
    public function setProperty($name, $value, array $scope = [])
    {
        return $this->loop->fill($this->async->setProperty($name, $scope));
    }

    /**
     * {@inheritdoc}
     */
    public function unsetProperty($name, array $scope = [])
    {
        return $this->loop->fill($this->async->unsetProperty($name, $scope));
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertyKeys($name, array $scope = [])
    {
        return $this->loop->fill($this->async->getPropertyKeys($name, $scope));
    }

    /**
     * {@inheritdoc}
     */
    public function subscribe($event, $callback)
    {
        return $this->loop->fill($this->async->subscribe($event, $callback));
    }

    /**
     * {@inheritdoc}
     */
    public function unsubscribe($event, $callback)
    {
        return $this->loop->fill($this->async->unsubscribe($event, $callback));
    }

    /**
     * {@inheritdoc}
     */
    public function onCommand($command, $network, $callback = null)
    {
        return $this->loop->fill($this->async->onCommand($command, $network, $callback));
    }

    /**
     * Retrieve the asynchronous DaZeus instance that is powering this instance
     * @return DaZeusAsync
     */
    public function getAsync()
    {
        return $this->async;
    }

    /**
     * Let the asynchronous DaZeus block on incomming events and responses
     * @return void
     */
    public function run()
    {
        $this->getAsync()->run();
    }

    public function fill($promise)
    {
        return $this->getAsync()->fill($promise);
    }
}
