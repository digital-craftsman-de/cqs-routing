<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Utility;

use DigitalCraftsman\CQRS\Test\ValueObject\UserId;

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
