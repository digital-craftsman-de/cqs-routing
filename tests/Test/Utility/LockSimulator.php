<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Utility;

final class LockSimulator
{
    /** @var array<int, string> */
    public array $lockedActions = [];

    /** @var array<int, string> */
    public array $unlockedActions = [];

    public function lockAction(string $id): void
    {
        $this->lockedActions[] = $id;
    }

    public function unlockAction(string $id): void
    {
        $this->unlockedActions[] = $id;
    }
}
