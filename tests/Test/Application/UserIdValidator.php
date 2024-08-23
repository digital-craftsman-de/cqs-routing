<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Application;

use DigitalCraftsman\CQSRouting\Command\Command;
use DigitalCraftsman\CQSRouting\DTOValidator\DTOValidatorInterface;
use DigitalCraftsman\CQSRouting\Query\Query;
use DigitalCraftsman\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle\CreateNewsArticleCommand;
use DigitalCraftsman\CQSRouting\Test\Utility\SecuritySimulator;
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
