<?php
declare(strict_types=1);

namespace ThenLabs\TaskLoop\Condition;

use DateTime;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class TimeInterval extends AbstractCondition
{
    /**
     * @var DateTime
     */
    protected $dateTime;

    public function __construct(string $value)
    {
        $this->dateTime = new DateTime($value);
    }

    public function setDateTime(DateTime $dateTime): void
    {
        $this->dateTime = $dateTime;
    }

    public function getDateTime(): DateTime
    {
        return $this->dateTime;
    }

    public function update(): void
    {
        $now = new DateTime();

        if ($now >= $this->dateTime) {
            $this->setFulfilled(true);
        }
    }
}
