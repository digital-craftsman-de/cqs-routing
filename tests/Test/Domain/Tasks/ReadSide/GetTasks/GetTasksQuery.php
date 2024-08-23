<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Domain\Tasks\ReadSide\GetTasks;

use DigitalCraftsman\CQSRouting\Query\Query;
use DigitalCraftsman\CQSRouting\Test\ValueObject\ActionId;
use DigitalCraftsman\CQSRouting\Test\ValueObject\UserId;

final readonly class GetTasksQuery implements Query
{
    public function __construct(
        public UserId $userId,
        public ActionId $actionId,
    ) {
    }
}
