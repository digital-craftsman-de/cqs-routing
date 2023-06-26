<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Domain\News\WriteSide\CreateNewsArticle;

use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\Test\ValueObject\UserId;

final readonly class CreateNewsArticleCommand implements Command
{
    public function __construct(
        public UserId $userId,
        public string $title,
        public string $content,
        public bool $isPublished,
    ) {
    }
}
