<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Domain\Tasks\ReadSide\GetTasks;

use DigitalCraftsman\CQRS\Query\Query;
use DigitalCraftsman\CQRS\Query\QueryHandlerInterface;

/** @psalm-immutable */
final class GetTasksQueryHandler implements QueryHandlerInterface
{
    /** @param GetTasksQuery $query */
    public function handle(Query $query): array
    {
        // Query tasks

        return [];
    }
}
