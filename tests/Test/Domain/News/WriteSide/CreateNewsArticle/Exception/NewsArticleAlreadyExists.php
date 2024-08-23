<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle\Exception;

/** @psalm-immutable */
final class NewsArticleAlreadyExists extends \DomainException
{
    public function __construct()
    {
        parent::__construct('This news article already exists');
    }
}
