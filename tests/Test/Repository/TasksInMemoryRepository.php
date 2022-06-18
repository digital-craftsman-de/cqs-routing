<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Repository;

use DigitalCraftsman\CQRS\Test\Entity\Task;

final class TasksInMemoryRepository
{
    /**
     * @var array<int, Task>
     * @psalm-readonly-allow-private-mutation
     */
    public array $tasks;

    /** @param array<int, Task> $tasks */
    public function __construct(
        array $tasks = [],
    ) {
        $this->tasks = $tasks;
    }

    public function store(Task $task): void
    {
        $this->tasks[] = $task;
    }

    /** @return array<int, Task> */
    public function findAll(): array
    {
        return $this->tasks;
    }
}
