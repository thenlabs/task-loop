<?php

require_once __DIR__.'/vendor/autoload.php';

use ThenLabs\TaskLoop\{AbstractTask, TaskLoop};

define('DATE_FORMAT', 'Y-m-d H:i:s');

// create a loop instance.
$loop = new TaskLoop();

// adds the task1.
$loop->addTask(new class extends AbstractTask {

    protected $counter = 10;

    public function run(): void
    {
        echo date(DATE_FORMAT)." Task1: {$this->counter}\n";
        $this->counter--;

        if ($this->counter <= 0) {
            echo date(DATE_FORMAT)." Task1: End\n\n";

            // when task1 ends, the loop will be stopped.
            $this->loop->stop();
        }
    }
});

// adds the task2.
$loop->addTask(new class extends AbstractTask {

    protected $counter = 5;

    public function run(): void
    {
        echo date(DATE_FORMAT)." Task2: {$this->counter}\n";
        $this->counter--;

        if ($this->counter <= 0) {
            echo date(DATE_FORMAT)." Task2: End\n\n";

            // when task2 ends, will be dropped from the loop.
            $this->loop->dropTask($this);
        }
    }
});

$delay = 1000000; // value for the usleep function.
$loop->start($delay);

echo 'Good bye!';