<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Utility;

final class ConnectionSimulator
{
    /** @psalm-readonly-allow-private-mutation */
    public bool $hasActiveTransaction = false;

    /** @psalm-readonly-allow-private-mutation */
    public bool $hasCommitted = false;

    public function beginTransaction(): void
    {
        $this->hasActiveTransaction = true;
    }

    public function commit(): void
    {
        $this->hasCommitted = true;
        $this->hasActiveTransaction = false;
    }

    public function rollBack(): void
    {
        $this->hasActiveTransaction = false;
    }
}
