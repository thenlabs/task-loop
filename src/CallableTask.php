<?php
declare(strict_types=1);

namespace ThenLabs\TaskLoop;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class CallableTask extends AbstractTask
{
    /**
     * @var callable
     */
    protected $callable;

    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    public function run(): void
    {
        call_user_func($this->callable, $this);
    }
}
