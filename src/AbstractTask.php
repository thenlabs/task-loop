<?php
declare(strict_types=1);

namespace ThenLabs\TaskLoop;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class AbstractTask implements TaskInterface
{
    /**
     * @var TaskLoop
     */
    protected $loop;

    public function getTaskLoop(): TaskLoop
    {
        return $this->loop;
    }

    public function setTaskLoop(TaskLoop $loop): void
    {
        $this->loop = $loop;
    }

    public function end(): void
    {
        $this->loop->dropTask($this);
    }
}
