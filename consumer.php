<?php

require_once 'vendor/autoload.php';

use Spiral\RoadRunner\Jobs\Consumer;
use Spiral\RoadRunner\Jobs\Task\Factory\ReceivedTaskFactory;
use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;
use Spiral\RoadRunner\Worker;

$worker = Worker::create();
$receivedTaskFactory = new ReceivedTaskFactory($worker);
$consumer = new Consumer($worker, $receivedTaskFactory);

/** @var ReceivedTaskInterface $task */
while ($task = $consumer->waitTask()) {
    $task->complete();
}
