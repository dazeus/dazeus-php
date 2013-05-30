<?php

namespace DaZeus;

interface DaZeusInterface
{
    const GROUP_CORE = 'core';
    const GROUP_PLUGIN = 'plugin';

    /**
     * Retrieve the networks the bot is connected to.
     */
    public function networks();
    public function channels($network);
    public function message($network, $channel, $message);
    public function action($network, $channel, $message);
    public function reply($network, $channel, $nick, $message, $highlight = true, $action = false);
    public function sendNames($network, $channel);
    public function names($network, $channel);
    public function sendWhois($network, $nick);
    public function whois($network, $nick);
    public function join($network, $channel);
    public function part($network, $channel);
    public function nick($network);
    public function doHandshake($name, $version, $config);
    public function getConfig($name, $group = self::GROUP_PLUGIN);
    public function getHighlightCharacter();
    public function getProperty($name, array $scope = []);
    public function setProperty($name, $value, array $scope = []);
    public function unsetProperty($name, array $scope = []);
    public function getPropertyKeys($name, array $scope = []);
    public function subscribe($event, $callback);
    public function unsubscribe($event, $callback);
    public function onCommand($command, $network, $callback = null);
}
