<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Entity;

use DigitalCraftsman\CQSRouting\Test\ValueObject\TaskId;
use DigitalCraftsman\CQSRouting\Test\ValueObject\UserId;

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
