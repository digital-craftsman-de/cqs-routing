<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Domain\Tasks\WriteSide\MarkTaskAsAccepted;

use DigitalCraftsman\CQSRouting\Command\Command;
use DigitalCraftsman\CQSRouting\Test\ValueObject\TaskId;

final readonly class MarkTaskAsAcceptedCommand implements Command
{
    public function __construct(
        public TaskId $taskId,
    ) {
    }
}
