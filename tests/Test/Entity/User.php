<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Entity;

use DigitalCraftsman\CQRS\Test\ValueObject\UserId;

final class User
{
    /** @psalm-readonly */
    public UserId $id;

    public string $emailAddress;
}
