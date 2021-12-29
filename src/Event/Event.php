<?php
declare(strict_types=1);

namespace ThenLabs\TaskLoop\Event;

use Symfony\Contracts\EventDispatcher\Event as SymfonyEvent;
use ThenLabs\TaskLoop\Task;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Event extends SymfonyEvent
{
    /**
     * @var Task
     */
    protected $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function getTask(): Task
    {
        return $this->task;
    }
}
