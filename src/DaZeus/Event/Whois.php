<?php

namespace DaZeus\Event;

class Whois extends Event
{
    public function getNetwork()
    {
        return $this['params'][0];
    }

    public function getNick()
    {
        return $this['params'][2];
    }
}
