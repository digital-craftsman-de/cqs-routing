<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\CreateTask;

use DigitalCraftsman\CQRS\Command\Command;

/** @psalm-immutable */
final class CreateTaskCommand implements Command
{
    public function __construct(
       public string $title,
       public string $content,
       public string $priority,
    ) {
    }
}
