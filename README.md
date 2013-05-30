# DaZeus PHP Bindings
PHP Bindings for DaZeus

## Setup
At least PHP 5.4.0 is required. These bindings use [composer][], download it
from their site, you can then do:

    composer install dazeus/dazeus-php

PHP should run fine without any additional modules or extensions.

## Usage
You can run these bindings in either asynchronous mode or in synchonous mode. In asynchonous
mode operations aren't blocking, but you'll have to use the `React\Promise\PromiseInterface`
interface for handling responses. The synchonous version of the API bindings blocks and waits
for the results of an operation and directly returns that result.

Events are all wrapped in an arraylike structure that you can use just like an array, but has
some additional methods for specific events (such as `Message` events where you can directly
reply to the message by calling its `reply()`, `highlight()` or `action()` methods).

Some examples are available in the `examples/` folder.

[composer]: http://getcomposer.org/
[react]: http://reactphp.org/
[libevent]: http://libevent.org/
