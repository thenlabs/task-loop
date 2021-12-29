<?php

use ThenLabs\TaskLoop\AbstractTask;
use ThenLabs\TaskLoop\Event\NewTaskEvent;
use ThenLabs\TaskLoop\Event\DropTaskEvent;
use ThenLabs\TaskLoop\Event\RunTaskEvent;
use ThenLabs\TaskLoop\TaskInterface;
use ThenLabs\TaskLoop\TaskLoop;

testCase(function () {
    setUp(function () {
        $this->loop = new TaskLoop();
    });

    test(function () {
        $this->assertCount(0, $this->loop->getTasks());
    });

    test(function () {
        $this->expectException(TypeError::class);

        $this->loop->addTask(uniqid());
    });

    test(function () {
        $task1 = $this->getMockBuilder(TaskInterface::class)
            ->setMethods(['run'])
            ->getMock();
        $task1->expects($this->once())
            ->method('run')
        ;

        $task2 = $this->getMockBuilder(TaskInterface::class)
            ->setMethods(['run'])
            ->getMock();
        $task2->expects($this->once())
            ->method('run')
        ;

        $task3 = $this->getMockBuilder(TaskInterface::class)
            ->setMethods(['run'])
            ->getMock();
        $task3->expects($this->once())
            ->method('run')
        ;

        $this->loop->addTask($task1);
        $this->loop->addTask($task2);
        $this->loop->addTask($task3);

        // Act
        $this->loop->runTasks();
    });

    test(function () {
        $task1 = new class($this->loop) implements TaskInterface {
            public $invokations = [];
            protected $loop;

            public function __construct(TaskLoop $loop)
            {
                $this->loop = $loop;
            }

            public function run(): void
            {
                $this->invokations[] = new DateTime();
            }
        };

        $task2 = new class($this->loop) implements TaskInterface {
            public $invokations = [];
            protected $loop;

            public function __construct(TaskLoop $loop)
            {
                $this->loop = $loop;
            }

            public function run(): void
            {
                $this->invokations[] = new DateTime();

                if (count($this->invokations) >= 3) {
                    $this->loop->stop();
                }
            }
        };

        $this->loop->addTask($task1);
        $this->loop->addTask($task2);

        $this->loop->start(1);

        $this->assertCount(3, $task1->invokations);
        $this->assertCount(3, $task2->invokations);
    });

    test(function () {
        $executed = false;

        $this->loop->addTask(function ($task) use (&$executed) {
            $executed = true;
            $task->end();
        });

        $this->loop->runTasks();

        $this->assertCount(0, $this->loop->getTasks());
    });

    test(function () {
        $this->loop->getDispatcher()->addListener(RunTaskEvent::class, function (RunTaskEvent $event) {
            $event->getTask()->runned = new DateTime();
        });

        $newTask = $this->createMock(TaskInterface::class);
        $this->loop->addTask($newTask);

        // Act
        $this->loop->runTasks();

        $this->assertInstanceOf(DateTime::class, $newTask->runned);
    });

    test(function () {
        $this->loop->getDispatcher()->addListener(RunTaskEvent::class, function (RunTaskEvent $event) {
            $event->setCancelled(true);
        });

        $this->loop->addTask($task = new class implements TaskInterface {
            public $executed = false;

            public function run(): void
            {
                $this->executed = true;
            }
        });

        // Act
        $this->loop->runTasks();

        $this->assertFalse($task->executed);
    });

    test(function () {
        $this->loop->getDispatcher()->addListener(NewTaskEvent::class, function (NewTaskEvent $event) {
            $event->getTask()->added = new DateTime();
        });

        $newTask = $this->createMock(TaskInterface::class);

        // Act
        $this->loop->addTask($newTask);

        $this->assertInstanceOf(DateTime::class, $newTask->added);
    });

    test(function () {
        $this->loop->getDispatcher()->addListener(NewTaskEvent::class, function ($event) {
            $event->setCancelled(true);
        });

        $newTask = $this->createMock(TaskInterface::class);

        // Act
        $this->loop->addTask($newTask);

        $this->assertCount(0, $this->loop->getTasks());
    });

    test(function () {
        $task = new class extends AbstractTask {
            protected $counter = 0;

            public function run(): void
            {
                echo ++$this->counter;

                if ($this->counter >= 3) {
                    $this->loop->stop();
                }
            }
        };

        $this->loop->addTask($task);

        ob_start();
        $this->loop->start(1);
        $output = ob_get_clean();

        $this->assertStringContainsString('123', $output);
        $this->assertStringNotContainsString('4', $output);
    });

    testCase(function () {
        setUp(function () {
            $this->dummyTask = $this->createMock(TaskInterface::class);

            $this->loop->addTask($this->dummyTask);
        });

        test(function () {
            $this->assertCount(1, $this->loop->getTasks());
            $this->assertTrue($this->loop->getTasks()->contains($this->dummyTask));
        });

        test(function () {
            $this->loop->getDispatcher()->addListener(DropTaskEvent::class, function (DropTaskEvent $event) {
                $event->getTask()->dropped = new DateTime();
            });

            // Act
            $this->loop->dropTask($this->dummyTask);

            $this->assertCount(0, $this->loop->getTasks());
            $this->assertFalse($this->loop->getTasks()->contains($this->dummyTask));
            $this->assertInstanceOf(DateTime::class, $this->dummyTask->dropped);
        });

        test(function () {
            $this->loop->getDispatcher()->addListener(DropTaskEvent::class, function ($event) {
                $event->setCancelled(true);
            });

            // Act
            $this->loop->dropTask($this->dummyTask);

            $this->assertCount(1, $this->loop->getTasks());
            $this->assertTrue($this->loop->getTasks()->contains($this->dummyTask));
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
