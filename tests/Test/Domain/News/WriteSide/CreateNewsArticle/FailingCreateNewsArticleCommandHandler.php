<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Domain\News\WriteSide\CreateNewsArticle;

use DigitalCraftsman\CQRS\Command\CommandHandlerInterface;
use DigitalCraftsman\CQRS\Test\Domain\News\WriteSide\CreateNewsArticle\Exception\NewsArticleAlreadyExists;

final readonly class FailingCreateNewsArticleCommandHandler implements CommandHandlerInterface
{
    public function __invoke(CreateNewsArticleCommand $command): void
    {
        // Some logic that validates it

        throw new NewsArticleAlreadyExists();
    }
}
