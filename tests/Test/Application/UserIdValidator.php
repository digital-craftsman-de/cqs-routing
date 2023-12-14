<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Application;

use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\DTOValidator\DTOValidatorInterface;
use DigitalCraftsman\CQRS\Query\Query;
use DigitalCraftsman\CQRS\Test\Domain\News\WriteSide\CreateNewsArticle\CreateNewsArticleCommand;
use DigitalCraftsman\CQRS\Test\Utility\SecuritySimulator;
use Symfony\Component\HttpFoundation\Request;

final class UserIdValidator implements DTOValidatorInterface
{
    public function __construct(
        private readonly SecuritySimulator $securitySimulator,
    ) {
    }

    /**
     * @param CreateNewsArticleCommand $dto
     * @param null                     $parameters
     */
    public function validateDTO(
        Request $request,
        Command | Query $dto,
        mixed $parameters,
    ): void {
        if ($this->securitySimulator->getAuthenticatedUserId()->isNotEqualTo($dto->userId)) {
            throw new \DomainException('Supplied user id is invalid');
        }
    }

    /** @param null $parameters */
    public static function areParametersValid(mixed $parameters): bool
    {
        return $parameters === null;
    }
}
