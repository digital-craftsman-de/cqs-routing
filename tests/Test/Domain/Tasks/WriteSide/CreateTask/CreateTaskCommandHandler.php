<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\CreateTask;

use DigitalCraftsman\CQRS\Command\CommandHandlerInterface;

final class CreateTaskCommandHandler implements CommandHandlerInterface
{
    public function __invoke(CreateTaskCommand $command): void
    {
        // Create new task and store it ...
    }
}
