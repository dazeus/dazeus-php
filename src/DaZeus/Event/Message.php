<?php

namespace DaZeus\Event;

class Message extends Event
{
    public function reply($message)
    {
        return $this->dazeus->reply(
            $this->getNetwork(),
            $this->getChannel(),
            $this->getNick(),
            $message,
            false,
            false
        );
    }

    public function highlight($message)
    {
        return $this->dazeus->reply(
            $this->getNetwork(),
            $this->getChannel(),
            $this->getNick(),
            $message,
            true,
            false
        );
    }

    public function action($message)
    {
        return $this->dazeus->reply(
            $this->getNetwork(),
            $this->getChannel(),
            $this->getNick(),
            $message,
            false,
            true
        );
    }

    public function getNick()
    {
        return $this['params'][1];
    }

    public function getMessage()
    {
        return $this['params'][3];
    }

    public function getChannel()
    {
        return $this['params'][2];
    }

    public function getNetwork()
    {
        return $this['params'][0];
    }
}
