<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Utility;

use DigitalCraftsman\CQSRouting\Test\ValueObject\UserId;

final class SecuritySimulator
{
    private ?UserId $authenticatedUserId = null;

    public function fixateAuthenticatedUserId(UserId $userId): void
    {
        $this->authenticatedUserId = $userId;
    }

    public function getAuthenticatedUserId(): UserId
    {
        if ($this->authenticatedUserId === null) {
            throw new \DomainException('Authenticated user id is not fixated');
        }

        return $this->authenticatedUserId;
    }
}
