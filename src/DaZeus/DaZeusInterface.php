<?php

namespace DaZeus;

/**
 * Interface for interacting with DaZeus
 */
interface DaZeusInterface
{
    const GROUP_CORE = 'core';
    const GROUP_PLUGIN = 'plugin';

    /**
     * Retrieve the networks the bot is connected to
     */
    public function networks();

    /**
     * Retrieve the channels the bot has joined on a given network
     * @param  string $network
     */
    public function channels($network);

    /**
     * Send a message to a channel on a network
     * @param  string $network
     * @param  string $channel
     * @param  string $message
     */
    public function message($network, $channel, $message);

    /**
     * Send an action (/me) message to a channel on a network
     * @param  string $network
     * @param  string $channel
     * @param  string $message
     */
    public function action($network, $channel, $message);

    /**
     * Reply with a message
     * This automatically checks for queries and can add a highlight for the user
     * to be responded to.
     * @param  string  $network
     * @param  string  $channel
     * @param  string  $nick
     * @param  string  $message
     * @param  boolean $highlight
     * @param  boolean $action
     */
    public function reply($network, $channel, $nick, $message, $highlight = true, $action = false);

    /**
     * Send a request for names on a channel
     * @param  string $network
     * @param  string $channel
     */
    public function sendNames($network, $channel);

    /**
     * Send a request for names on a channel and retrieve a response
     * @param  string $network
     * @param  string $channel
     */
    public function names($network, $channel);

    /**
     * Send a whois request for a nick on a network
     * @param  string $network
     * @param  string $nick
     */
    public function sendWhois($network, $nick);

    /**
     * Send a whois request for a nick on a network and retrieve a response
     * @param  string $network
     * @param  string $nick
     */
    public function whois($network, $nick);

    /**
     * Join a channel on a network
     * @param  string $network
     * @param  string $channel
     */
    public function join($network, $channel);

    /**
     * Leave a channel on a network
     * @param  string $network
     * @param  string $channel
     */
    public function part($network, $channel);

    /**
     * Retrieve the nick the bot has on a network
     * @param  string $network
     */
    public function nick($network);

    /**
     * Do a protocol handshake with the dazeus server
     * This makes configuration variables available for the connecting client.
     * @param  string  $name
     * @param  integer $version
     * @param  string  $config
     */
    public function doHandshake($name, $version, $config);

    /**
     * Retrieve a configuration value for the given group
     * The group can be either 'core' for core configuration values or
     * 'plugin' for configuration values related to the plugin.
     * @param  string $name
     * @param  string $group
     */
    public function getConfig($name, $group = self::GROUP_PLUGIN);

    /**
     * Retrieve the character by which the bot is notified of a command
     */
    public function getHighlightCharacter();

    /**
     * Retrieve a property in the storage of the dazeus server
     * Scope may be an array of [network, receiver, sender] where receiver is the
     * channel the property applies to and sender the user in that channel. You may
     * also set a property only limited to the network or to a [network, receiver]
     * combination.
     * @param  string $name
     * @param  array  $scope
     */
    public function getProperty($name, array $scope = []);

    /**
     * Set a property to a new value in the storage of the dazeus server
     * Scope may be an array of [network, receiver, sender] where receiver is the
     * channel the property applies to and sender the user in that channel. You may
     * also set a property only limited to the network or to a [network, receiver]
     * combination.
     * @param string $name
     * @param mixed  $value
     * @param array  $scope
     */
    public function setProperty($name, $value, array $scope = []);

    /**
     * Remove a property from the dazeus server storage
     * Scope may be an array of [network, receiver, sender] where receiver is the
     * channel the property applies to and sender the user in that channel. You may
     * also set a property only limited to the network or to a [network, receiver]
     * combination.
     * @param  string $name
     * @param  array  $scope
     */
    public function unsetProperty($name, array $scope = []);

    /**
     * Retrieve the keys that have a prefix of the given name with the given scope
     * Scope may be an array of [network, receiver, sender] where receiver is the
     * channel the property applies to and sender the user in that channel. You may
     * also set a property only limited to the network or to a [network, receiver]
     * combination.
     * @param  string $name
     * @param  array  $scope
     */
    public function getPropertyKeys($name, array $scope = []);

    /**
     * Subscribe to a new event with the given callback
     * The callback will be called every time an event of the given type is called.
     * Note that you may register a callback for multiple events by separating the
     * event codes by spaces.
     * @param  string   $event
     * @param  function $callback
     */
    public function subscribe($event, $callback);

    /**
     * Unsubscribe a callback from being called when an event occurs
     * @param  string   $event
     * @param  function $callback
     */
    public function unsubscribe($event, $callback);

    /**
     * Register a new command to the server
     * If a network is specified, the command will only be registered on that network,
     * if no callback is specified, the network parameter is instead assumed to be the
     * actual callback.
     * @param  string   $command
     * @param  string   $network
     * @param  function $callback
     */
    public function onCommand($command, $network, $callback = null);
}
