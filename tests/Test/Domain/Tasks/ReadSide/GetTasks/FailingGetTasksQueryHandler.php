<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Domain\Tasks\ReadSide\GetTasks;

use DigitalCraftsman\CQRS\Query\Query;
use DigitalCraftsman\CQRS\Query\QueryHandlerInterface;
use DigitalCraftsman\CQRS\Test\Domain\Tasks\ReadSide\GetTasks\Exception\TasksNotAccessible;

/** @psalm-immutable */
final class FailingGetTasksQueryHandler implements QueryHandlerInterface
{
    /** @param GetTasksQuery $query */
    public function handle(Query $query): array
    {
        throw new TasksNotAccessible();
    }
}
