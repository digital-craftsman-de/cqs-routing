<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Entity;

use DigitalCraftsman\CQSRouting\Test\ValueObject\UserId;

final class User
{
    /** @psalm-readonly */
    public UserId $id;

    public string $emailAddress;
}
