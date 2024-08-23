<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Domain\Tasks\WriteSide\DefineTaskHourContingent;

use DigitalCraftsman\CQSRouting\Command\Command;

final readonly class DefineTaskHourContingentCommand implements Command
{
    public function __construct(
        public float $hourContingent,
    ) {
    }
}
