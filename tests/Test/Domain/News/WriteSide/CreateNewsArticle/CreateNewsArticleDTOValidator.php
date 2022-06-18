<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Domain\News\WriteSide\CreateNewsArticle;

use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\DTOValidator\DTOValidatorInterface;
use DigitalCraftsman\CQRS\Query\Query;
use DigitalCraftsman\CQRS\Test\Security\SecuritySimulator;
use Symfony\Component\HttpFoundation\Request;

final class CreateNewsArticleDTOValidator implements DTOValidatorInterface
{
    public function __construct(
        private SecuritySimulator $securitySimulator,
    ) {
    }

    /** @param CreateNewsArticleCommand $dto */
    public function validateDTO(
        Request $request,
        Command|Query $dto,
    ): void {
        if ($this->securitySimulator->getAuthenticatedUserId()->isNotEqualTo($dto->userId)) {
            throw new \DomainException('Supplied user id is invalid');
        }
    }
}
