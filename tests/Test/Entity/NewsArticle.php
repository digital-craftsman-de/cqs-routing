<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Entity;

use DigitalCraftsman\CQSRouting\Test\ValueObject\NewsArticleId;
use DigitalCraftsman\CQSRouting\Test\ValueObject\UserId;

final class NewsArticle
{
    public function __construct(
        /** @psalm-readonly */
        public NewsArticleId $newsArticleId,
        public UserId $createdByUserId,
        public string $title,
        public string $content,
        public bool $isPublished,
    ) {
    }
}
