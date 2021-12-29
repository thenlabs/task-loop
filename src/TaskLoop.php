<?php
declare(strict_types=1);

namespace ThenLabs\TaskLoop;

use SplObjectStorage;
use Symfony\Component\EventDispatcher\EventDispatcher;
use TypeError;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class TaskLoop
{
    /**
     * @var boolean
     */
    protected $started = false;

    /**
     * @var SplObjectStorage
     */
    protected $tasks;

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    public function __construct()
    {
        $this->tasks = new SplObjectStorage();
        $this->dispatcher = new EventDispatcher();
    }

    public function getDispatcher(): EventDispatcher
    {
        return $this->dispatcher;
    }

    public function getTasks(): SplObjectStorage
    {
        return $this->tasks;
    }

    /**
     * @param TaskInterface|callable $task
     * @return void
     */
    public function addTask($task): void
    {
        if (! $task instanceof TaskInterface) {
            if (is_callable($task)) {
                $task = new CallableTask($task);
            } else {
                throw new TypeError('The task argument should be an instance of TaskInterface or a callable.');
            }
        }

        $event = new Event\NewTaskEvent($task);
        $this->dispatcher->dispatch($event);

        if ($event->isCancelled()) {
            return;
        }

        $this->tasks->attach($task);

        if ($task instanceof AbstractTask) {
            $task->setTaskLoop($this);
        }
    }

    public function dropTask(TaskInterface $task): void
    {
        $event = new Event\DropTaskEvent($task);
        $this->dispatcher->dispatch($event);

        if ($event->isCancelled()) {
            return;
        }

        $this->tasks->detach($task);
    }

    public function start(int $delay): void
    {
        $this->loopStarted = true;

        while ($this->loopStarted) {
            $this->runTasks();

            usleep($delay);
        }
    }

    public function stop(): void
    {
        $this->loopStarted = false;
    }

    public function runTasks(): void
    {
        foreach ($this->tasks as $task) {
            $event = new Event\RunTaskEvent($task);
            $this->dispatcher->dispatch($event);

            if ($event->isCancelled()) {
                continue;
            }

            $task->run();
        }
    }
}
