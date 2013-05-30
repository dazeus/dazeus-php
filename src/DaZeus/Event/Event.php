<?php

namespace DaZeus\Event;

use ArrayObject;
use DaZeus\DaZeusInterface;

class Event extends ArrayObject
{
    const CONNECT = 'CONNECT';
    const DISCONNECT = 'DISCONNECT';
    const JOIN = 'JOIN';
    const PART = 'PART';
    const QUIT = 'QUIT';
    const NICK = 'NICK';
    const MODE = 'MODE';
    const TOPIC = 'TOPIC';
    const INVITE = 'INVITE';
    const KICK = 'KICK';
    const PRIVMSG = 'PRIVMSG';
    const NOTICE = 'NOTICE';
    const CTCP = 'CTCP';
    const CTCP_REP = 'CTCP_REP';
    const ACTION = 'ACTION';
    const NUMERIC = 'NUMERIC';
    const UNKNOWN = 'UNKNOWN';
    const WHOIS = 'WHOIS';
    const NAMES = 'NAMES';
    const PRIVMSG_ME = 'PRIVMSG_ME';
    const CTCP_ME = 'CTCP_ME';
    const ACTION_ME = 'ACTION_ME';
    const PONG = 'PONG';
    const COMMAND = 'COMMAND';

    public $dazeus;

    public $event;

    public function __construct(array $event, DaZeusInterface $instance)
    {
        parent::__construct($event);
        $this->dazeus = $instance;
        $this->init();
    }

    public function init()
    {
        $this->event = $this['event'];
    }
}
