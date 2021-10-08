<?php
declare(strict_types=1);

namespace ThenLabs\Loop;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
interface TaskInterface
{
    public function run(): void;
}