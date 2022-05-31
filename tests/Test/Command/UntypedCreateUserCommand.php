<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Command;

use DigitalCraftsman\CQRS\Command\Command;

/** @psalm-immutable */
final class UntypedCreateUserCommand implements Command
{
    public function __construct(
        public $id,
        public $emailAddress,
        public $name,
        public $registrationReference,
    ) {
    }
}
