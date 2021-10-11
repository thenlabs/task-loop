<?php
declare(strict_types=1);

namespace ThenLabs\TaskLoop;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
interface TaskInterface
{
    public function run(): void;
}
