<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Command;

interface CommandHandlerInterface
{
    public function handle(Command $command): void;
}
