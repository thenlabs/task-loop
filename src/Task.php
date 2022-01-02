<?php
declare(strict_types=1);

namespace ThenLabs\TaskLoop;

use Generator;
use ThenLabs\TaskLoop\Condition\AbstractCondition;
use ThenLabs\TaskLoop\Condition\TimeInterval;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Task
{
    /**
     * @var Loop
     */
    protected $loop;

    /**
     * @var callable
     */
    protected $callable;

    /**
     * @var array<AbstractCondition>
     */
    protected $conditions = [];

    /**
     * @var bool
     */
    protected $started = false;

    /**
     * @var bool
     */
    protected $ended = false;

    /**
     * @var mixed
     */
    protected $result;

    public function __construct(Loop $loop, callable $callable)
    {
        $this->loop = $loop;
        $this->callable = $callable;
    }

    public function getLoop(): Loop
    {
        return $this->loop;
    }

    public function getCallable(): callable
    {
        return $this->callable;
    }

    public function end($result = null): void
    {
        $event = new Event\EndTaskEvent($this);
        $event->setResult($result);

        $dispatcher = $this->loop->getDispatcher();
        $dispatcher->dispatch($event);

        $this->loop->dropTask($this);

        $this->started = false;
        $this->ended = true;
        $this->result = $result;
    }

    public function run(...$arguments)
    {
        if (! $this->started) {
            $this->started = true;
        }

        foreach ($this->getUnfulfilledConditions() as $condition) {
            $condition->update();

            if (! $condition->isFulfilled()) {
                return;
            }
        }

        $args = [$this, ...$arguments];

        if ($this->callable instanceof Generator) {
            $generator = $this->callable;

            if ($generator->valid()) {
                $result = $generator->send($args);

                if (! $generator->valid()) {
                    $result = $generator->getReturn();
                    $this->end($result);
                }
            } else {
                $result = $generator->getReturn();
                $this->end($result);
            }
        } else {
            $result = call_user_func_array($this->callable, $args);
        }

        if ($result instanceof Generator) {
            $generator = $result;

            $this->callable = $generator;

            $this->result = $generator->current();
            return $this->result;
        } else {
            if ($this->ended) {
                return $this->result;
            } else {
                $this->result = $result;

                return $result;
            }
        }
    }

    public function __invoke(...$arguments)
    {
        call_user_func_array([$this, 'run'], $arguments);
    }

    public function addCondition(AbstractCondition $condition): void
    {
        $this->conditions[] = $condition;
    }

    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function getUnfulfilledConditions(): array
    {
        $result = [];

        foreach ($this->conditions as $condition) {
            if (! $condition->isFulfilled()) {
                $result[] = $condition;
            }
        }

        return $result;
    }

    public function isStarted(): bool
    {
        return $this->started;
    }

    public function isEnded(): bool
    {
        return $this->ended;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function addDelay(string $value): void
    {
        $this->addCondition(new TimeInterval($value));
    }
}
