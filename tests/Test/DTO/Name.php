<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\DTO;

/** @psalm-immutable */
final class Name
{
    public function __construct(
        public string $firstName,
        public string $lastName,
    ) {
    }
}
