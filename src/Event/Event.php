<?php
declare(strict_types=1);

namespace ThenLabs\TaskLoop\Event;

use ThenLabs\TaskLoop\TaskInterface;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Event extends \Symfony\Contracts\EventDispatcher\Event
{
    /**
     * @var TaskInterface
     */
    protected $task;

    /**
     * @var boolean
     */
    protected $cancelled = false;

    public function __construct(TaskInterface $task)
    {
        $this->task = $task;
    }

    public function getTask(): TaskInterface
    {
        return $this->task;
    }

    public function isCancelled(): bool
    {
        return $this->cancelled;
    }

    public function setCancelled(bool $cancelled): void
    {
        $this->cancelled = $cancelled;
    }
}
