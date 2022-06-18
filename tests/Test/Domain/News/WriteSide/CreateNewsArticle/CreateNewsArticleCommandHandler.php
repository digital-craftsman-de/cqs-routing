<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Domain\News\WriteSide\CreateNewsArticle;

use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\Command\CommandHandlerInterface;
use DigitalCraftsman\CQRS\Test\Entity\NewsArticle;
use DigitalCraftsman\CQRS\Test\Repository\NewsArticleInMemoryRepository;
use DigitalCraftsman\CQRS\Test\ValueObject\NewsArticleId;

/** @psalm-immutable */
final class CreateNewsArticleCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private NewsArticleInMemoryRepository $newsArticleInMemoryRepository,
    ) {
    }

    /** @param CreateNewsArticleCommand $command */
    public function handle(Command $command): void
    {
        $newsArticle = new NewsArticle(
            NewsArticleId::generateRandom(),
            $command->userId,
            $command->title,
            $command->content,
            $command->isPublished,
        );

        $this->newsArticleInMemoryRepository->store($newsArticle);
    }
}
