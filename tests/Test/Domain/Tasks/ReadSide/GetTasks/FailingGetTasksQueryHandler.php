<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Domain\Tasks\ReadSide\GetTasks;

use DigitalCraftsman\CQSRouting\Query\QueryHandler;
use DigitalCraftsman\CQSRouting\Test\Domain\Tasks\ReadSide\GetTasks\Exception\TasksNotAccessible;

final readonly class FailingGetTasksQueryHandler implements QueryHandler
{
    public function __invoke(GetTasksQuery $query): array
    {
        throw new TasksNotAccessible();
    }
}
