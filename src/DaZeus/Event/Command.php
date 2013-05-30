<?php

namespace DaZeus\Event;

/**
 * Command event
 */
class Command extends Message
{
    public $command;

    public $args;

    public $remainder;

    public function init()
    {
        parent::init();
        $this->remainder = isset($this['params'][4]) ? $this['params'][4] : '';
        $this->message = $this->dazeus->fill($this->dazeus->getHighlightCharacter()) .
                         $this->message .
                         (strlen($this->remainder) > 0 ? ' ' . $this->remainder : '');
        $this->command = $this['params'][3];
        $this->args = [];
        for ($i = 5; $i < count($this['params']); $i += 1) {
            $this->args[] = $this['params'][$i];
        }
    }
}
