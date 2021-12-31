<?php
declare(strict_types=1);

namespace ThenLabs\TaskLoop;

use SplObjectStorage;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
     * @var SplObjectStorage<Task>
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

    public function addTask(callable $task): void
    {
        $task = $task instanceof Task ? $task : new Task($this, $task);

        $event = new Event\NewTaskEvent($task);
        $this->dispatcher->dispatch($event);

        $this->tasks->attach($task);
    }

    public function dropTask(Task $task): void
    {
        $event = new Event\DropTaskEvent($task);
        $this->dispatcher->dispatch($event);

        $this->tasks->detach($task);
    }

    public function start(int $delay, bool $endOnEmpty = true): void
    {
        $this->loopStarted = true;

        while ($this->loopStarted) {
            $this->runAll();

            usleep($delay);

            if ($endOnEmpty && 0 === count($this->tasks)) {
                break;
            }
        }
    }

    public function stop(): void
    {
        $this->loopStarted = false;
    }

    protected function runTask(Task $task): void
    {
        $beforeEvent = new Event\BeforeTaskEvent($task);
        $this->dispatcher->dispatch($beforeEvent);

        if ($beforeEvent->isCancelled()) {
            return;
        }

        $result = $task->run();

        $afterEvent = new Event\AfterTaskEvent($task);
        $afterEvent->setResult($result);

        $this->dispatcher->dispatch($afterEvent);
    }

    public function runAll(): void
    {
        foreach ($this->tasks as $task) {
            $this->runTask($task);
        }
    }

    public function runOne(): void
    {
        $task = $this->tasks->current();

        if ($task instanceof Task) {
            $this->runTask($task);

            $this->tasks->next();

            if (! $this->tasks->valid()) {
                $this->tasks->rewind();
            }
        }
    }

    public function on(string $eventName, callable $listener): void
    {
        $this->dispatcher->addListener($eventName, $listener);
    }
}
