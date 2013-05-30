<?php

namespace DaZeus\Event;

use DaZeus\Event\Event as Events;

class EventAlias
{
    protected static $aliases = [
        'MESSAGE' => Events::PRIVMSG,
        'MESSAGE_ME' => Events::PRIVMSG_ME,
        'RENAME' => Events::NICK,
        'CTCPREP' => Events::CTCP_REP,
        'MESSAGEME' => Events::PRIVMSG_ME,
        'PRIVMSGME' => Events::PRIVMSG_ME,
        'ACTIONME' => Events::ACTION_ME,
        'CTCPME' => Events::CTCP_ME,
    ];

    public static function resolve($alias)
    {
        if (in_array(strtoupper($alias), self::$aliases)) {
            return self::$aliases[$alias];
        }

        return $alias;
    }

    public static function addAlias($from, $to)
    {
        self::$aliases[$from] = $to;
    }
}
