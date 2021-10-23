<?php

require_once __DIR__.'/vendor/autoload.php';

use ThenLabs\TaskLoop\{TaskLoop, CallableTask};

define('DATE_FORMAT', 'Y-m-d H:i:s');

// create a loop instance.
$loop = new TaskLoop();

// adds the task1.
$loop->addTask(function (CallableTask $task) {
    static $counter = 10;

    echo date(DATE_FORMAT)." Task1: {$counter}\n";
    $counter--;

    if ($counter <= 0) {
        echo date(DATE_FORMAT)." Task1: End\n\n";

        // when task1 ends, the loop will be stopped.
        $task->getTaskLoop()->stop();
    }
});

// adds the task2.
$loop->addTask(function (CallableTask $task) {
    static $counter = 5;

    echo date(DATE_FORMAT)." Task2: {$counter}\n";
    $counter--;

    if ($counter <= 0) {
        echo date(DATE_FORMAT)." Task2: End\n\n";

        // when task2 ends, will be dropped from the loop.
        $task->end();
    }
});

$delay = 1000000; // value for the usleep function.
$loop->start($delay);

echo 'Good bye!';