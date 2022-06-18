<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Domain\Tasks\ReadSide\GetTasks;

use DigitalCraftsman\CQRS\Query\Query;
use DigitalCraftsman\CQRS\Query\QueryHandlerInterface;
use DigitalCraftsman\CQRS\Test\Repository\TasksInMemoryRepository;

/** @psalm-immutable */
final class GetTasksQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private TasksInMemoryRepository $tasksInMemoryRepository,
    ) {
    }

    /** @param GetTasksQuery $query */
    public function handle(Query $query): array
    {
        return $this->tasksInMemoryRepository->findAll();
    }
}
