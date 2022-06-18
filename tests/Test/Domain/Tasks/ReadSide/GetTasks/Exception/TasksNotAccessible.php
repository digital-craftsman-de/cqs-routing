<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Domain\Tasks\ReadSide\GetTasks\Exception;

/** @psalm-immutable */
final class TasksNotAccessible extends \DomainException
{
    public function __construct()
    {
        parent::__construct('You do not have access to this tasks');
    }
}
