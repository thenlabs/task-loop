<?php
declare(strict_types=1);

namespace ThenLabs\TaskLoop\Event;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class EndTaskEvent extends Event
{
    use ResultTrait;
}
