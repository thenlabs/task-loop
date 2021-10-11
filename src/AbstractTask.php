<?php
declare(strict_types=1);

namespace ThenLabs\TaskLoop;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class AbstractTask implements TaskInterface
{
    use TaskLoopTrait;
}
