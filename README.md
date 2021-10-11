
# TaskLoop

A PHP implementation of a bare task loop.

## Installation.

    $ composer require thenlabs/task-loop

## Usage.

The file `example.php` contains the below code which show that once the loop is started, it will runs each one of his tasks.

Each loop iteration is executed with a time interval which it's specified with the `start()` method.

Like can be seen, for create the tasks exists the class `ThenLabs\TaskLoop\AbstractTask` which has some useful methods, but any instance of the interface `ThenLabs\TaskLoop\TaskInterface` may be considered as a task.

```php
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
```

That file produce the next result:

```
2021-10-09 11:30:31 Task1: 10
2021-10-09 11:30:31 Task2: 5
2021-10-09 11:30:32 Task1: 9
2021-10-09 11:30:32 Task2: 4
2021-10-09 11:30:33 Task1: 8
2021-10-09 11:30:33 Task2: 3
2021-10-09 11:30:34 Task1: 7
2021-10-09 11:30:34 Task2: 2
2021-10-09 11:30:35 Task1: 6
2021-10-09 11:30:35 Task2: 1
2021-10-09 11:30:35 Task2: End

2021-10-09 11:30:36 Task1: 5
2021-10-09 11:30:37 Task1: 4
2021-10-09 11:30:38 Task1: 3
2021-10-09 11:30:39 Task1: 2
2021-10-09 11:30:40 Task1: 1
2021-10-09 11:30:40 Task1: End

Good bye!
```

## Development.

### Running the tests.

For run the tests, runs the next command:

    $ ./vendor/bin/pyramidal
