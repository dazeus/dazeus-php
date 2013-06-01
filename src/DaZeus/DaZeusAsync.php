<?php

namespace DaZeus;

use DaZeus\Filler\FillerInterface;
use DaZeus\Filler\FillerLoopInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use React\Promise\Deferred;
use React\Promise\FulfilledPromise;
use React\Promise\When;

class DaZeusAsync implements DaZeusInterface, FillerInterface
{
    const PROTOCOL = 1;

    protected $conn;

    protected $logger;

    protected $handshake;

    protected $subscribers;

    public function __construct(Connection $connection, LoggerInterface $logger = null)
    {
        $this->conn = $connection;
        if ($logger === null) {
            $logger = new NullLogger();
        }
        $this->logger = $logger;
        $this->handshake = false;
        $this->subscribers = [];
        $this->conn->on('message', [$this, 'handleEvent']);
    }

    /**
     * {@inheritdoc}
     */
    public function networks()
    {
        $data = [
            'get' => 'networks',
        ];
        return $this->conn->send($data)->then(function ($response) {
            if ($response['success'] === true) {
                return $response['networks'];
            } else {
                return [];
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function channels($network)
    {
        $data = [
            'get' => 'channels',
            'params' => [$network],
        ];
        return $this->conn->send($data)->then(function ($response) {
            if ($response['success'] === true) {
                return $response['channels'];
            } else {
                return [];
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function message($network, $channel, $message)
    {
        $data = [
            'do' => 'message',
            'params' => [$network, $channel, $message],
        ];
        return $this->conn->send($data)->then(function ($response) {
            return $response['success'] === true;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function action($network, $channel, $message)
    {
        $data = [
            'do' => 'action',
            'params' => [$network, $channel, $message],
        ];
        return $this->conn->send($data)->then(function ($response) {
            return $response['success'] === true;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function reply($network, $channel, $nick, $message, $highlight = true, $action = false)
    {
        $deferred = new Deferred();
        $this->nick($network)->then(function ($mynick)
          use ($network, $channel, $nick, $message, $highlight, $action, $deferred) {
            if ($channel === $mynick) {
                if ($action === true) {
                    $promise = $this->action($network, $nick, $message);
                } else {
                    $promise = $this->message($network, $nick, $message);
                }
            } else {
                if ($highlight === true) {
                    $message = $nick . ': ' . $message;
                }
                if ($action === true) {
                    $promise = $this->action($network, $channel, $message);
                } else {
                    $promise = $this->message($network, $channel, $message);
                }
            }
            $promise->then(function ($response) use ($deferred) {
                $deferred->resolve($response);
            });
        });
        return $deferred;
    }

    /**
     * {@inheritdoc}
     */
    public function sendNames($network, $channel)
    {
        $data = [
            'do' => 'names',
            'params' => [$network, $channel],
        ];
        return $this->conn->send($data)->then(function ($response) {
            return $response['success'] === true;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function names($network, $channel)
    {
        $deferred = new Deferred();
        $self = $this;
        $callback = null;
        $callback = function (Event\Names $names) use (&$callback, $network, $channel, $deferred, $self) {
            if ($names->getNetwork() === $network && $names->getChannel() === $channel) {
                $deferred->resolve($names);
                $self->unsubscribe('NAMES', $callback);
            }
        };
        $this->subscribe('NAMES', $callback);
        $this->sendNames($network, $channel);
        return $deferred->promise();
    }

    /**
     * {@inheritdoc}
     */
    public function sendWhois($network, $nick)
    {
        $data = [
            'do' => 'whois',
            'params' => [$network, $nick],
        ];
        return $this->conn->send($data)->then(function ($response) {
            return $response['success'] === true;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function whois($network, $nick)
    {
        $deferred = new Deferred();
        $self = $this;
        $callback = null;
        $callback = function (Event\Whois $names) use (&$callback, $network, $nick, $deferred, $self) {
            if ($names->getNetwork() === $network && $names->getNick() === $nick) {
                $deferred->resolve($names);
                $self->unsubscribe('WHOIS', $callback);
            }
        };
        $this->subscribe('WHOIS', $callback);
        $this->sendWhois($network, $nick);
        return $deferred->promise();
    }

    /**
     * {@inheritdoc}
     */
    public function join($network, $channel)
    {
        $data = [
            'do' => 'join',
            'params' => [$network, $channel]
        ];

        return $this->conn->send($data)->then(function ($response) {
            return $response['success'] === true;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function part($network, $channel)
    {
        $data = [
            'do' => 'part',
            'params' => [$network, $channel]
        ];

        return $this->conn->send($data)->then(function ($response) {
            return $response['success'] === true;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function nick($network)
    {
        $data = [
            'get' => 'nick',
            'params' => [$network],
        ];
        return $this->conn->send($data)->then(function ($response) {
            if ($response['success'] === true) {
                return $response['nick'];
            } else {
                return null;
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function doHandshake($name, $version, $config)
    {
        $data = [
            'do' => 'handshake',
            'params' => [$name, $version, self::PROTOCOL, $config],
        ];
        $self = $this;
        return $this->conn->send($data)->then(function ($response) use ($self) {
            if ($response['success'] === true) {
                $this->handshake = true;
                return true;
            } else {
                return false;
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig($name, $group = self::GROUP_PLUGIN)
    {
        if ($group === self::GROUP_CORE || $this->handshake) {
            $data = [
                'get' => 'config',
                'params' => [$group, $name],
            ];
            return $this->conn->send($data)->then(function ($response) {
                if ($response['success'] === true && isset($response['value'])) {
                    return $response['value'];
                } else {
                    return null;
                }
            });
        } else {
            return new FulfilledPromise(null);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function highlightCharacter()
    {
        return $this->getConfig('highlight', self::GROUP_CORE);
    }

    /**
     * {@inheritdoc}
     */
    public function getProperty($name, array $scope = [])
    {
        $data = [
            'do' => 'property',
            'params' => ['get', $name],
        ];
        if (count($scope) > 0) {
            $data['scope'] = $scope;
        }
        return $this->conn->send($data)->then(function ($response) {
            if ($response['success'] === true && isset($response['value'])) {
                return $response['value'];
            } else {
                return null;
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function setProperty($name, $value, array $scope = [])
    {
        $data = [
            'do' => 'property',
            'params' => ['set', $name, $value],
        ];
        if (count($scope) > 0) {
            $data['scope'] = $scope;
        }
        return $this->conn->send($data)->then(function ($response) {
            return $response['success'] === true;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function unsetProperty($name, array $scope = [])
    {
        $data = [
            'do' => 'property',
            'params' => ['unset', $name],
        ];
        if (count($scope) > 0) {
            $data['scope'] = $scope;
        }
        return $this->conn->send($data)->then(function ($response) {
            return $response['success'] === true;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertyKeys($name, array $scope = [])
    {
        $data = [
            'do' => 'property',
            'params' => ['keys', $name],
        ];
        if (count($scope) > 0) {
            $data['scope'] = $scope;
        }
        return $this->conn->send($data)->then(function ($response) {
            if ($response['success'] === true && isset($response['keys'])) {
                return $response['keys'];
            } else {
                return [];
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function subscribe($event, $callback)
    {
        $events = explode(' ', strtoupper($event));

        $promises = [];
        foreach ($events as $event) {
            $promises[] = $this->subscribeSingle($event, $callback);
        }

        return When::all($promises, function ($results) {
            foreach ($results as $result) {
                if ($result === false) {
                    return false;
                }
            }
            return true;
        });
    }

    protected function subscribeSingle($event, $callback)
    {
        $event = Event\EventAlias::resolve($event);
        if (!isset($this->subscribers[$event])) {
            $this->subscribers[$event] = [];
        }

        if (count($this->subscribers[$event]) === 0) {
            $data = [
                'do' => 'subscribe',
                'params' => [$event],
            ];
            $promise = $this->conn->send($data)->then(function ($response) {
                return $response['success'] === true;
            });
        } else {
            $promise = new FulfilledPromise(true);
        }
        $this->subscribers[$event][] = $callback;
        return $promise;
    }

    /**
     * {@inheritdoc}
     */
    public function unsubscribe($event, $callback)
    {
        $events = explode(' ', strtoupper($event));

        $promises = [];
        foreach ($events as $event) {
            $promises[] = $this->unsubscribeSingle($event, $callback);
        }

        return When::all($promises, function ($results) {
            foreach ($results as $result) {
                if ($result === false) {
                    return false;
                }
            }
            return true;
        });
    }

    protected function unsubscribeSingle($event, $callback)
    {
        $event = Event\EventAlias::resolve($event);
        if (!isset($this->subscribers[$event])) {
            $this->subscribers[$event] = [];
        }

        $count = count($this->subscribers[$event]);

        for ($i = count($this->subscribers[$event]) - 1; $i >= 0; $i -= 1) {
            if ($this->subscribers[$event][$i] === $callback) {
                array_splice($this->subscribers[$event], $i, 1);
            }
        }

        if ($count > 0 && count($this->subscribers[$event]) === 0) {
            $data = [
                'do' => 'unsubscribe',
                'params' => [$event],
            ];
            $promise = $this->conn->send($data)->then(function ($response) {
                return $response['success'] === true;
            });
        } else {
            $promise = new FulfilledPromise(true);
        }
        return $promise;
    }

    public function handleEvent(array $message)
    {
        if (isset($message['event'])) {
            $event = strtoupper($message['event']);
            switch ($event) {
                case Event\Event::PRIVMSG:
                case Event\Event::PRIVMSG_ME:
                    $object = new Event\Message($message, $this);
                    break;
                case Event\Event::COMMAND:
                    $object = new Event\Command($message, $this);
                    break;
                case Event\Event::ACTION:
                case Event\Event::ACTION_ME:
                    $object = new Event\Action($message, $this);
                    break;
                case Event\Event::NAMES:
                    $object = new Event\Names($message, $this);
                    break;
                case Event\Event::WHOIS:
                    $object = new Event\Whois($message, $this);
                    break;
                default:
                    $object = new Event\Event($message, $this);
                    break;
            }

            if ($event === Event\Event::COMMAND) {
                foreach ($this->subscribers[Event\Event::COMMAND][$object['params'][3]] as $subscriber) {
                    if ($subscriber[0] === null || $subscriber[0] === $object['params'][0]) {
                        $callback = $subscriber[1];
                        $callback($object);
                    }
                }
            } else {
                foreach ($this->subscribers[$event] as $subscriber) {
                    $subscriber($object);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onCommand($command, $network, $callback = null)
    {
        if (!isset($this->subscribers[Event\Event::COMMAND])) {
            $this->subscribers[Event\Event::COMMAND] = [];
        }

        if ($callback === null) {
            $callback = $network;
            $network = null;
        }

        if (!isset($this->subscribers[Event\Event::COMMAND][$command])) {
            $this->subscribers[Event\Event::COMMAND][$command] = [[$network, $callback]];
            $data = [
                'do' => 'command',
                'params' => [$command],
            ];
            $promise = $this->conn->send($data)->then(function ($response) {
                return $response['success'] === true;
            });
        } else {
            $this->subscribers[Event\Event::COMMAND][$command][] = [$network, $callback];
            $promise = new FulfilledPromise(true);
        }
        return $promise;
    }

    /**
     * Start the EventLoop
     * @return void
     */
    public function run()
    {
        $this->getLoop()->run();
    }

    /**
     * Retrieve the EventLoop
     * @return FillerLoopInterface
     */
    public function getLoop()
    {
        return $this->conn->getLoop();
    }

    /**
     * {@inheritdoc}
     */
    public function fill($promise)
    {
        return $this->getLoop()->fill($promise);
    }
}
