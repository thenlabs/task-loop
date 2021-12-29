<?php
declare(strict_types=1);

namespace ThenLabs\TaskLoop\Event;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
trait ResultTrait
{
    /**
     * @var mixed
     */
    protected $result;

    public function setResult($result): void
    {
        $this->result = $result;
    }

    public function getResult()
    {
        return $this->result;
    }
}
