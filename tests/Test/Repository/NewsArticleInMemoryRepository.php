<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Repository;

use DigitalCraftsman\CQSRouting\Test\Entity\NewsArticle;

final class NewsArticleInMemoryRepository
{
    /**
     * @var array<int, NewsArticle>
     *
     * @psalm-readonly-allow-private-mutation
     */
    public array $newsArticles = [];

    public function store(NewsArticle $newsArticle): void
    {
        $this->newsArticles[] = $newsArticle;
    }
}
