<?php

namespace DaZeus\Event;

class Whois extends Event
{
    public $network;

    public $nick;

    public function init()
    {
        parent::init();
        $this->network = $this['params'][0];
        $this->nick = $this['params'][2];
    }
}
