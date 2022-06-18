<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\MarkTaskAsAccepted\Exception;

/** @psalm-immutable */
final class TaskAlreadyAccepted extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Task was already marked as accepted');
    }
}
