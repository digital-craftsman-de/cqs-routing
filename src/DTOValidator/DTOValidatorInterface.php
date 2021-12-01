<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\DTOValidator;

use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\Query\Query;
use DigitalCraftsman\CQRS\Workflow\Workflow;
use Symfony\Component\HttpFoundation\Request;

interface DTOValidatorInterface
{
    public function validateDTO(Request $request, Command|Query|Workflow $dto): void;
}
