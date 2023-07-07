<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Domain\News\WriteSide\CreateNewsArticle;

use DigitalCraftsman\CQRS\Command\CommandHandlerInterface;
use DigitalCraftsman\CQRS\Test\Entity\NewsArticle;
use DigitalCraftsman\CQRS\Test\Repository\NewsArticleInMemoryRepository;
use DigitalCraftsman\CQRS\Test\ValueObject\NewsArticleId;

final readonly class CreateNewsArticleCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private NewsArticleInMemoryRepository $newsArticleInMemoryRepository,
    ) {
    }

    public function __invoke(CreateNewsArticleCommand $command): void
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
