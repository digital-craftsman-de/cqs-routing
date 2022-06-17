<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Domain\Tasks\ReadSide\GetTasks;

use DigitalCraftsman\CQRS\Query\Query;

/** @psalm-immutable */
final class GetTasksQuery implements Query
{
    public function __construct(
        public string $userId,
    ) {
    }
}
