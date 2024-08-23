<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle;

use DigitalCraftsman\CQSRouting\Command\CommandHandlerInterface;
use DigitalCraftsman\CQSRouting\Test\Entity\NewsArticle;
use DigitalCraftsman\CQSRouting\Test\Repository\NewsArticleInMemoryRepository;
use DigitalCraftsman\CQSRouting\Test\ValueObject\NewsArticleId;

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
