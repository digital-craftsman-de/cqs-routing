<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Domain\Tasks\WriteSide\CreateTask;

use DigitalCraftsman\CQSRouting\Command\CommandHandlerInterface;

final class CreateTaskCommandHandler implements CommandHandlerInterface
{
    public function __invoke(CreateTaskCommand $command): void
    {
        // Create new task and store it ...
    }
}
