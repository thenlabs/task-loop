<?php
declare(strict_types=1);

namespace ThenLabs\TaskLoop\Condition;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class AbstractCondition
{
    /**
     * @var bool
     */
    protected $fulfilled = false;

    public function isFulfilled(): bool
    {
        return $this->fulfilled;
    }

    public function setFulfilled(bool $fulfilled): void
    {
        $this->fulfilled = $fulfilled;
    }

    abstract public function update(): void;
}
