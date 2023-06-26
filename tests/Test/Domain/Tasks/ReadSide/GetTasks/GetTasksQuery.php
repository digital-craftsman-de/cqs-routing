<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Domain\Tasks\ReadSide\GetTasks;

use DigitalCraftsman\CQRS\Query\Query;
use DigitalCraftsman\CQRS\Test\ValueObject\ActionId;
use DigitalCraftsman\CQRS\Test\ValueObject\UserId;

final readonly class GetTasksQuery implements Query
{
    public function __construct(
        public UserId $userId,
        public ActionId $actionId,
    ) {
    }
}
