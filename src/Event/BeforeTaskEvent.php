<?php
declare(strict_types=1);

namespace ThenLabs\TaskLoop\Event;

use ThenLabs\Components\Event\CancellableTrait;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class BeforeTaskEvent extends Event
{
    use CancellableTrait;
}
