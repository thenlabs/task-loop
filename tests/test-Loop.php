<?php

use ThenLabs\TaskLoop\Event\AfterTaskEvent;
use ThenLabs\TaskLoop\Event\BeforeTaskEvent;
use ThenLabs\TaskLoop\Event\DropTaskEvent;
use ThenLabs\TaskLoop\Event\EndTaskEvent;
use ThenLabs\TaskLoop\Event\NewTaskEvent;
use ThenLabs\TaskLoop\Loop;
use ThenLabs\TaskLoop\Task;

testCase(function () {
    setUp(function () {
        $this->loop = new Loop();
    });

    test(function () {
        $this->assertCount(0, $this->loop->getTasks());
    });

    test(function () {
        $this->invokations1 = [];
        $this->invokations2 = [];

        $this->loop->addTask(function () {
            $this->invokations1[] = new DateTime();
        });

        $this->loop->addTask(function (Task $task) {
            $this->invokations2[] = new DateTime();

            if (count($this->invokations2) >= 3) {
                $task->getLoop()->stop();
            }
        });

        $this->loop->start(1);

        $this->assertCount(3, $this->invokations1);
        $this->assertCount(3, $this->invokations1);
    });

    test(function () {
        $executed = false;

        $this->loop->addTask(function ($task) use (&$executed) {
            $executed = true;
            $task->end();
        });

        $this->loop->runAll();

        $this->assertCount(0, $this->loop->getTasks());
    });

    test(function () {
        $this->loop->on(NewTaskEvent::class, function (NewTaskEvent $event) {
            $event->getTask()->added = new DateTime();
        });

        $this->loop->addTask(function () {
        });
        $task = $this->loop->getTasks()->current();

        $this->assertInstanceOf(DateTime::class, $task->added);
    });

    test(function () {
        $this->counter = 0;

        $this->loop->addTask(function () {
            echo ++$this->counter;

            if ($this->counter >= 3) {
                $this->loop->stop();
            }
        });

        ob_start();
        $this->loop->start(1);
        $output = ob_get_clean();

        $this->assertStringContainsString('123', $output);
        $this->assertStringNotContainsString('4', $output);
    });

    test(function () {
        $this->executedEvent = false;
        $this->value = uniqid();

        $this->loop->addTask(function ($task) {
            $task->end($this->value);
        });

        $this->task = $this->loop->getTasks()->current();

        $this->loop->on(EndTaskEvent::class, function (EndTaskEvent $event) {
            $this->executedEvent = true;
            $this->assertSame($this->task, $event->getTask());
        });

        $this->loop->runAll();

        $this->assertTrue($this->executedEvent);
        $this->assertCount(0, $this->loop->getTasks());
    });

    test(function () {
        $this->executedEndTaskEvent = false;
        $this->result = null;

        $this->loop->addTask(function () {
            $this->result = 1;
            yield $this->result;

            $this->result = 2;
            yield $this->result;

            $this->result = 3;
            return $this->result; // ends task
        });

        $this->task = $this->loop->getTasks()->current();

        $this->loop->on(RunTaskEvent::class, function ($event) {
            $this->assertSame($this->result, $event->getResult());
        });

        $this->loop->on(EndTaskEvent::class, function ($event) {
            $this->assertSame(3, $event->getResult());

            $this->executedEndTaskEvent = true;
        });

        $this->loop->runOne();
        $this->assertSame(1, $this->result);
        $this->assertFalse($this->executedEndTaskEvent);
        $this->assertCount(1, $this->loop->getTasks());

        $this->loop->runOne();
        $this->assertSame(2, $this->result);
        $this->assertFalse($this->executedEndTaskEvent);
        $this->assertCount(1, $this->loop->getTasks());

        $this->loop->runOne();
        $this->assertSame(3, $this->result);
        $this->assertTrue($this->executedEndTaskEvent);
        $this->assertCount(0, $this->loop->getTasks());
    });

    testCase(function () {
        setUp(function () {
            $this->executedTask1 = false;
            $this->executedTask2 = false;
            $this->executedTask3 = false;

            $this->loop->addTask(function () {
                $this->executedTask1 = true;
            });

            $this->loop->addTask(function () {
                $this->executedTask2 = true;
            });

            $this->loop->addTask(function () {
                $this->executedTask3 = true;
            });
        });

        test(function () {
            $this->loop->runAll();

            $this->assertTrue($this->executedTask1);
            $this->assertTrue($this->executedTask2);
            $this->assertTrue($this->executedTask3);
        });

        test(function () {
            $this->assertFalse($this->executedTask1);
            $this->assertFalse($this->executedTask2);
            $this->assertFalse($this->executedTask3);

            $this->loop->runOne();

            $this->assertTrue($this->executedTask1);
            $this->assertFalse($this->executedTask2);
            $this->assertFalse($this->executedTask3);

            $this->loop->runOne();

            $this->assertTrue($this->executedTask1);
            $this->assertTrue($this->executedTask2);
            $this->assertFalse($this->executedTask3);

            $this->loop->runOne();

            $this->assertTrue($this->executedTask1);
            $this->assertTrue($this->executedTask2);
            $this->assertTrue($this->executedTask3);
        });
    });

    testCase(function () {
        setUp(function () {
            $this->moment = [];

            $this->loop->addTask(function () {
                $this->moment['task'] = new DateTime();
                return $this->moment['task'];
            });

            $this->task = $this->loop->getTasks()->current(); // returns the unique task.
        });

        testCase(function () {
            setUp(function () {
                $this->loop->on(BeforeTaskEvent::class, function (BeforeTaskEvent $event) {
                    $this->assertSame($this->task, $event->getTask());

                    $this->moment['before_event'] = new DateTime();
                });
            });

            test(function () {
                $this->loop->runAll();

                $this->assertInstanceOf(DateTime::class, $this->moment['task']);
                $this->assertInstanceOf(DateTime::class, $this->moment['before_event']);
                $this->assertTrue($this->moment['task'] > $this->moment['before_event']);
            });

            test(function () {
                $this->loop->runOne();

                $this->assertInstanceOf(DateTime::class, $this->moment['task']);
                $this->assertInstanceOf(DateTime::class, $this->moment['before_event']);
                $this->assertTrue($this->moment['task'] > $this->moment['before_event']);
            });
        });

        testCase(function () {
            setUp(function () {
                $this->loop->on(BeforeTaskEvent::class, function (BeforeTaskEvent $event) {
                    $this->moment['before_event'] = new DateTime();

                    $event->cancel();
                });
            });

            test(function () {
                $this->loop->runAll();

                $this->assertArrayNotHasKey('task', $this->moment);
                $this->assertInstanceOf(DateTime::class, $this->moment['before_event']);
            });

            test(function () {
                $this->loop->runOne();

                $this->assertArrayNotHasKey('task', $this->moment);
                $this->assertInstanceOf(DateTime::class, $this->moment['before_event']);
            });
        });

        testCase(function () {
            setUp(function () {
                $this->loop->on(AfterTaskEvent::class, function (AfterTaskEvent $event) {
                    $this->assertSame($this->task, $event->getTask());
                    $this->assertSame($this->moment['task'], $event->getResult());

                    $this->moment['after_event'] = new DateTime();
                });
            });

            test(function () {
                $this->loop->runAll();

                $this->assertInstanceOf(DateTime::class, $this->moment['task']);
                $this->assertInstanceOf(DateTime::class, $this->moment['after_event']);
                $this->assertTrue($this->moment['task'] < $this->moment['after_event']);
            });

            test(function () {
                $this->loop->runOne();

                $this->assertInstanceOf(DateTime::class, $this->moment['task']);
                $this->assertInstanceOf(DateTime::class, $this->moment['after_event']);
                $this->assertTrue($this->moment['task'] < $this->moment['after_event']);
            });
        });
    });

    testCase(function () {
        setUp(function () {
            $this->loop->addTask(function () {
            });
            $this->dummyTask = $this->loop->getTasks()->current();
        });

        test(function () {
            $this->assertCount(1, $this->loop->getTasks());
            $this->assertTrue($this->loop->getTasks()->contains($this->dummyTask));
        });

        test(function () {
            $this->loop->on(DropTaskEvent::class, function (DropTaskEvent $event) {
                $event->getTask()->dropped = new DateTime();
            });

            // Act
            $this->loop->dropTask($this->dummyTask);

            $this->assertCount(0, $this->loop->getTasks());
            $this->assertFalse($this->loop->getTasks()->contains($this->dummyTask));
            $this->assertInstanceOf(DateTime::class, $this->dummyTask->dropped);
        });

        testCase(function () {
            setUp(function () {
                $this->loop->dropTask($this->dummyTask);
            });

            test(function () {
                $this->assertCount(0, $this->loop->getTasks());
                $this->assertFalse($this->loop->getTasks()->contains($this->dummyTask));
            });
        });
    });
});
