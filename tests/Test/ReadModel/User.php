<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\ReadModel;

final readonly class User
{
    public function __construct(
        public string $userId,
        public string $name,
        public int $amountPayed,
        public bool $isEnabled,
    ) {
    }
}
