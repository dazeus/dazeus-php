<?php

namespace DaZeus;

use DaZeus\Filler\FillerLoopInterface;
use Evenement\EventEmitter;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use React\Socket\Connection as ReactConnection;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\FulfilledPromise;
use React\Promise\PromiseInterface;

/**
 * Backend connection to DaZeus instance
 */
class Connection extends EventEmitter
{
    protected $address;

    protected $loop;

    protected $logger;

    protected $waiters;

    /**
     * @param string              $address Address to directly connect to
     * @param FillerLoopInterface $loop
     * @param LoggerInterface     $logger
     */
    public function __construct($address = null, FillerLoopInterface $loop, LoggerInterface $logger = null)
    {
        $this->loop = $loop;
        $this->waiters = [];
        if ($logger === null) {
            $logger = new NullLogger();
        }
        $this->logger = $logger;
        if ($address !== null) {
            $this->connect($address);
        }
    }

    /**
     * Get the address currently connected to
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Create a connection with the given address
     * @param  string $address
     * @return void
     */
    public function connect($address)
    {
        $this->address = $address;
        $client = stream_socket_client($this->address);
        $this->conn = new ReactConnection($client, $this->loop);

        $this->conn->on('data', [$this, 'receive']);
        $this->on('message', [$this, 'handleWatchers']);
    }

    /**
     * Function that is called when new data arrives on the connection
     * Extracts the separate json messages inside the received data and
     * emits `message` events for each of those messages.
     * @param  string $data
     * @return void
     */
    public function receive($data)
    {
        $this->logger->debug("Received from socket: {message}", ['message' => trim($data)]);
        $len = strlen($data);
        $idx = 0;
        do {
            while ($idx < $len && ctype_space($data[$idx])) {
                $idx += 1;
            }

            if ($idx < $len) {
                $message_size = '';
                while (ctype_digit($data[$idx])) {
                    $message_size .= $data[$idx];
                    $idx += 1;
                }
                $message_size = (int) $message_size;
                $json = substr($data, $idx, $message_size);
                $this->logger->debug("Extracted message: {message}", ['message' => $json]);
                $this->emit('message', [json_decode($json, true), $this]);
                $idx += $message_size;
            }
        } while ($idx < $len);
    }

    protected function handleWatchers($message)
    {
        if (count($this->watchers) > 0 && !isset($message['event'])) {
            $resolver = array_shift($this->watchers);
            $resolver->resolve($message);
        }
    }

    protected function dazeusify($data)
    {
        $str = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $str = strlen($str) . $str . "\r\n";
        return $str;
    }

    protected function sendData($data)
    {
        $str = $this->dazeusify($data);
        $this->logger->debug("Sending: {message}", ['message' => trim($str)]);
        $this->conn->write($str);
    }

    /**
     * Transforms a message to the format requested by DaZeus and sends it
     * @param  mixed   $data     The data to be send, should be some stdObject or associative array
     * @param  boolean $callback Callback that receives the response, true for an
     *                           empty callback, false if no callback is expected
     * @return PromiseInterface
     */
    public function send($data, $callback = true)
    {
        if (is_callable($callback) || $callback === true) {
            $deferred = new Deferred();
            $deferred->then(function ($message) use ($callback) {
                if (is_callable($callback)) {
                    return $callback($message);
                }
                return $message;
            });
            $this->watchers[] = $deferred->resolver();
            $this->sendData($data);
            return $deferred->promise();
        } else {
            $this->sendData($data);
            return new FulfilledPromise();

        }
    }

    /**
     * Blocking version of the send method.
     * @param  mixed   $data     The data to be send, should be some stdObject or associative array
     * @param  boolean $callback Callback that receives the response, true for an empty callback,
     *                           false if no callback is expected
     * @return mixed
     */
    public function sendReceive($data, $callback = true)
    {
        $promise = $this->send($data, $callback);
        return $this->loop->fill($promise);
    }

    /**
     * Close the connection
     * @return void
     */
    public function close()
    {
        $this->conn->close();
        $this->emit('close');
    }

    /**
     * Retrieve the associated loop
     * @return FillerLoopInterface
     */
    public function getLoop()
    {
        return $this->loop;
    }
}
