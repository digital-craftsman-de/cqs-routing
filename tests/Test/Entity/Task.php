<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Entity;

use DigitalCraftsman\CQRS\Test\ValueObject\TaskId;
use DigitalCraftsman\CQRS\Test\ValueObject\UserId;

final class Task
{
    public function __construct(
        /** @psalm-readonly */
        public TaskId $taskId,
        public UserId $createdByUserId,
        public string $title,
    ) {
    }
}
