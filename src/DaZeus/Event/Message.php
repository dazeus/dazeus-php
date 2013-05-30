<?php

namespace DaZeus\Event;

/**
 * Message event (i.e. privmsg)
 */
class Message extends Event
{
    public $network;

    public $channel;

    public $nick;

    public $message;

    public function init()
    {
        parent::init();
        $this->network = $this['params'][0];
        $this->channel = $this['params'][1];
        $this->nick    = $this['params'][2];
        $this->message = $this['params'][3];

    }

    public function reply($message)
    {
        return $this->dazeus->reply(
            $this->network,
            $this->channel,
            $this->nick,
            $message,
            false,
            false
        );
    }

    public function highlight($message)
    {
        return $this->dazeus->reply(
            $this->network,
            $this->channel,
            $this->nick,
            $message,
            true,
            false
        );
    }

    public function action($message)
    {
        return $this->dazeus->reply(
            $this->network,
            $this->channel,
            $this->nick,
            $message,
            false,
            true
        );
    }
}
