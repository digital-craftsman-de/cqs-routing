<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Domain\Tasks\ReadSide\GetTasks;

use DigitalCraftsman\CQSRouting\Query\QueryHandler;
use DigitalCraftsman\CQSRouting\Test\Repository\TasksInMemoryRepository;

final readonly class GetTasksQueryHandler implements QueryHandler
{
    public function __construct(
        private TasksInMemoryRepository $tasksInMemoryRepository,
    ) {
    }

    public function __invoke(GetTasksQuery $query): array
    {
        return $this->tasksInMemoryRepository->findAll();
    }
}
