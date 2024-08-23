<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Domain\Tasks\WriteSide\MarkTaskAsAccepted;

use DigitalCraftsman\CQSRouting\Command\CommandHandlerInterface;
use DigitalCraftsman\CQSRouting\Test\Domain\Tasks\WriteSide\MarkTaskAsAccepted\Exception\TaskAlreadyAccepted;

final readonly class MarkTaskAsAcceptedCommandHandler implements CommandHandlerInterface
{
    public function handle(MarkTaskAsAcceptedCommand $command): void
    {
        // Task was already marked as accepted
        throw new TaskAlreadyAccepted();
        // Mark task as accepted...
    }
}
