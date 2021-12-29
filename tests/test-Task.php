<?php

use ThenLabs\TaskLoop\Condition\AbstractCondition;
use ThenLabs\TaskLoop\Loop;
use ThenLabs\TaskLoop\Task;

test(function () {
    $task = $this->getMockBuilder(Task::class)
        ->disableOriginalConstructor()
        ->setMethods(['run'])
        ->getMock();
    $task->expects($this->once())->method('run');

    $task();
});

testCase(function () {
    setUp(function () {
        $this->value1 = uniqid();
        $this->value2 = uniqid();

        $callable = function ($task, $arg1, $arg2) {
            $this->assertSame($arg1, $this->value1);
            $this->assertSame($arg2, $this->value2);
        };

        $this->task = new Task(new Loop(), $callable);
    });

    test(function () {
        $this->task->run($this->value1, $this->value2);
    });

    test(function () {
        call_user_func($this->task, $this->value1, $this->value2);
    });
});

test(function () {
    $registry = new stdClass;
    $registry->counter1 = 0;
    $registry->counter2 = 0;
    $registry->executed = false;

    $loop = new Loop();
    $callable = function () use ($registry) {
        $registry->executed = true;
    };

    $task = new Task($loop, $callable);

    $condition1 = new class($registry) extends AbstractCondition {
        protected $registry;

        public function __construct($registry)
        {
            $this->registry = $registry;
        }

        public function update(): void
        {
            $this->registry->counter1++;

            if ($this->registry->counter1 >= 3) {
                $this->setFulfilled(true);
            }
        }
    };

    $condition2 = new class($registry) extends AbstractCondition {
        protected $registry;

        public function __construct($registry)
        {
            $this->registry = $registry;
        }

        public function update(): void
        {
            $this->registry->counter2++;

            if ($this->registry->counter2 >= 2) {
                $this->setFulfilled(true);
            }
        }
    };

    $task->addCondition($condition1);
    $task->addCondition($condition2);

    $task->run();
    $this->assertEquals(1, $registry->counter1);
    $this->assertEquals(0, $registry->counter2);
    $this->assertFalse($registry->executed);

    $task->run();
    $this->assertEquals(2, $registry->counter1);
    $this->assertEquals(0, $registry->counter2);
    $this->assertFalse($registry->executed);

    $task->run();
    $this->assertEquals(3, $registry->counter1);
    $this->assertEquals(1, $registry->counter2);
    $this->assertFalse($registry->executed);

    $task->run();
    $this->assertEquals(3, $registry->counter1);
    $this->assertEquals(2, $registry->counter2);
    $this->assertTrue($registry->executed);

    $task->run();
    $this->assertEquals(3, $registry->counter1);
    $this->assertEquals(2, $registry->counter2);
    $this->assertTrue($registry->executed);
});

testCase(function () {
    setUp(function () {
        $this->dummy = new stdClass();
        $this->loop = new Loop();
        $this->callable = function () {
            $this->dummy->property = true;
        };

        $this->task = new Task($this->loop, $this->callable);
    });

    test(function () {
        call_user_func($this->task);

        $this->assertTrue($this->dummy->property);
    });

    test(function () {
        $this->task->run();

        $this->assertTrue($this->dummy->property);
    });
});

testCase(function () {
    setUp(function () {
        $this->loop = new Loop();
        $this->callable = function () {
        };

        $this->task = new Task($this->loop, $this->callable);
    });

    test(function () {
        $this->assertSame($this->loop, $this->task->getLoop());
    });

    test(function () {
        $this->assertSame($this->callable, $this->task->getCallable());
    });

    test(function () {
        $this->assertEmpty($this->task->getConditions());
    });

    test(function () {
        $this->assertEmpty($this->task->getUnfulfilledConditions());
    });

    testCase(function () {
        setUp(function () {
            $this->unfulfilledCondition = $this->createMock(AbstractCondition::class);
            $this->unfulfilledCondition->method('isFulfilled')->willReturn(false);

            $this->fulfilledCondition = $this->createMock(AbstractCondition::class);
            $this->fulfilledCondition->method('isFulfilled')->willReturn(true);

            $this->task->addCondition($this->unfulfilledCondition);
            $this->task->addCondition($this->fulfilledCondition);
        });

        test(function () {
            $expected = [
                $this->unfulfilledCondition,
                $this->fulfilledCondition,
            ];

            $this->assertSame($expected, $this->task->getConditions());
        });

        test(function () {
            $expected = [
                $this->unfulfilledCondition,
            ];

            $this->assertSame($expected, $this->task->getUnfulfilledConditions());
        });
    });
});
