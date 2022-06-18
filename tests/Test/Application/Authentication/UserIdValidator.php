<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Application\Authentication;

use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\DTOValidator\DTOValidatorInterface;
use DigitalCraftsman\CQRS\Query\Query;
use DigitalCraftsman\CQRS\Test\Application\Authentication\Exception\NotRelevantForDTO;
use DigitalCraftsman\CQRS\Test\Utility\SecuritySimulator;
use DigitalCraftsman\CQRS\Test\ValueObject\UserId;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UserIdValidator implements DTOValidatorInterface
{
    public function __construct(
        private SecuritySimulator $securitySimulator,
    ) {
    }

    public function validateDTO(
        Request $request,
        Command|Query $dto,
    ): void {
        $reflection = new \ReflectionClass($dto);
        if (!$reflection->hasProperty('userId')) {
            throw new NotRelevantForDTO($dto);
        }

        /**
         * @var UserId $userId
         * @psalm-suppress UndefinedPropertyFetch
         * @psalm-suppress NoInterfaceProperties
         */
        $userId = $dto->userId;

        if ($this->securitySimulator->getAuthenticatedUserId()->isNotEqualTo($userId)) {
            throw new \DomainException('Invalid user id', Response::HTTP_FORBIDDEN);
        }
    }
}
