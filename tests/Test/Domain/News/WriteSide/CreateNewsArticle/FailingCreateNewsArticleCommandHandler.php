<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Domain\News\WriteSide\CreateNewsArticle;

use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\Command\CommandHandlerInterface;
use DigitalCraftsman\CQRS\Test\Domain\News\WriteSide\CreateNewsArticle\Exception\NewsArticleAlreadyExists;

final class FailingCreateNewsArticleCommandHandler implements CommandHandlerInterface
{
    /** @param CreateNewsArticleCommand $command */
    public function handle(Command $command): void
    {
        // Some logic that validates it

        throw new NewsArticleAlreadyExists();
    }
}
