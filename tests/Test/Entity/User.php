<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Entity;

use DigitalCraftsman\CQRS\Test\ValueObject\UserId;
use Symfony\Component\Security\Core\User\UserInterface;

final class User implements UserInterface
{
    /** @psalm-readonly */
    public UserId $id;

    public string $emailAddress;

    public string $passwordHash;

    public function getRoles(): array
    {
        return [];
    }

    /** @see UserInterface */
    public function eraseCredentials(): void
    {
        // Not necessary
    }

    public function getUserIdentifier(): string
    {
        return $this->emailAddress;
    }
}
