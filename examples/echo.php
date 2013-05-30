<?php

require '../vendor/autoload.php';
set_time_limit(0);

$dazeus = DaZeus\Factory::create('unix:///tmp/dazeus.sock');
$dazeus->subscribe('message', function ($message) {
    $message->reply($message->getMessage());
});
