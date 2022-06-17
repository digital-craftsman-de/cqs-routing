<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\CreateTask;

use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\Command\CommandHandlerInterface;

final class CreateTaskCommandHandler implements CommandHandlerInterface
{
    /** @param CreateTaskCommand $command */
    public function handle(Command $command): void
    {
        // Create new task and store it ...
    }
}
