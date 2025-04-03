<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle;

use DigitalCraftsman\CQSRouting\Command\CommandHandler;
use DigitalCraftsman\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle\Exception\NewsArticleAlreadyExists;

final readonly class FailingCreateNewsArticleCommandHandler implements CommandHandler
{
    public function __invoke(CreateNewsArticleCommand $command): void
    {
        // Some logic that validates it

        throw new NewsArticleAlreadyExists();
    }
}
