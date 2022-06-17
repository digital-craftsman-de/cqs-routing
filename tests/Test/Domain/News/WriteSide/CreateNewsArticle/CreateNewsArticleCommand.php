<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Domain\News\WriteSide\CreateNewsArticle;

use DigitalCraftsman\CQRS\Command\Command;

/** @psalm-immutable */
final class CreateNewsArticleCommand implements Command
{
    public function __construct(
       public string $userId,
       public string $title,
       public string $content,
       public bool $isPublished,
    ) {
    }
}
