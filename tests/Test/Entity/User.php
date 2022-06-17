<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Entity;

use DigitalCraftsman\CQRS\Test\ValueObject\UserId;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

final class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'user_id')]
    /** @psalm-readonly */
    public UserId $id;

    #[ORM\Column(name: 'email_address', type: 'string')]
    public string $emailAddress;

    #[ORM\Column(name: 'password_hash', type: 'string')]
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
