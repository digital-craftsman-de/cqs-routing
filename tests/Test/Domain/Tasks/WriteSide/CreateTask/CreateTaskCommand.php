<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Domain\Tasks\WriteSide\CreateTask;

use DigitalCraftsman\CQSRouting\Command\Command;

final readonly class CreateTaskCommand implements Command
{
    public function __construct(
        public string $title,
        public string $content,
        public string $priority,
    ) {
    }
}
