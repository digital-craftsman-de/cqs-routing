<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\DTOConstructor\Command;

use DigitalCraftsman\CQRS\Command\Command;

/** @psalm-immutable */
final class CreateNewsArticleCommand extends Command
{
    public function __construct(
       public string $userId,
       public string $title,
       public string $content,
       public bool $isPublished,
    ) {
    }
}
