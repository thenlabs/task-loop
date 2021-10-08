<?php

use ThenLabs\Loop\Loop;
use ThenLabs\Loop\TaskInterface;

testCase(function () {
    setUp(function () {
        $this->loop = new Loop();
    });

    test(function () {
        $this->assertCount(0, $this->loop->getTasks());
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
    });
});