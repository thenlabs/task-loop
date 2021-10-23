<?php

use ThenLabs\TaskLoop\AbstractTask;
use ThenLabs\TaskLoop\TaskLoop;

test(function () {
    $task = new class extends AbstractTask {
        public function run(): void
        {
        }
    };

    $loop = $this->getMockBuilder(TaskLoop::class)
        ->setMethods(['dropTask'])
        ->getMock();
    $loop->expects($this->once())
        ->method('dropTask')
        ->with($this->equalTo($task))
    ;

    $loop->addTask($task);

    $task->end();
});