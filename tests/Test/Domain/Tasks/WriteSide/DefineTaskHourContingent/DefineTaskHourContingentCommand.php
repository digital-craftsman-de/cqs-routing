<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Domain\Tasks\WriteSide\DefineTaskHourContingent;

use DigitalCraftsman\CQRS\Command\Command;

final readonly class DefineTaskHourContingentCommand implements Command
{
    public function __construct(
        public float $hourContingent,
    ) {
    }
}
