<?php

use ThenLabs\TaskLoop\Condition\TimeInterval;

test(function () {
    $condition = new TimeInterval('-1 day');

    $condition->update();

    $this->assertTrue($condition->isFulfilled());
});

test(function () {
    $condition = new TimeInterval('+100 microseconds');

    $condition->update();
    $this->assertFalse($condition->isFulfilled());

    usleep(100);
    $condition->update();
    $this->assertTrue($condition->isFulfilled());
});
