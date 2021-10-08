<?php
declare(strict_types=1);

namespace ThenLabs\Loop;

use SplObjectStorage;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Loop
{
    /**
     * @var boolean
     */
    protected $started = false;

    /**
     * @var SplObjectStorage
     */
    protected $tasks;

    public function __construct()
    {
        $this->tasks = new SplObjectStorage();
    }

    public function addTask(TaskInterface $task): void
    {
        $this->tasks->attach($task);
    }

    public function dropTask(TaskInterface $task): void
    {
        $this->tasks->detach($task);
    }

    public function getTasks(): SplObjectStorage
    {
        return $this->tasks;
    }

    public function startLoop(int $delay): void
    {
        $this->loopStarted = true;

        while ($this->loopStarted) {
            $this->runTasks();

            usleep($delay);
        }
    }

    public function stopLoop(): void
    {
        $this->loopStarted = false;
    }

    public function runTasks(): void
    {
        foreach ($this->tasks as $task) {
            $task->run();
        }
    }
}