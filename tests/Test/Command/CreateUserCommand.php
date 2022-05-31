<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Command;

use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\Test\DTO\Name;

/** @psalm-immutable */
final class CreateUserCommand implements Command
{
    public function __construct(
        public string $id,
        public string $emailAddress,
        public ?Name $name,
        public ?string $registrationReference,
    ) {
    }
}
