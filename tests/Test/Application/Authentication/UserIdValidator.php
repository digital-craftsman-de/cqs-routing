<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Application\Authentication;

use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\DTOValidator\DTOValidatorInterface;
use DigitalCraftsman\CQRS\Query\Query;
use DigitalCraftsman\CQRS\Test\Application\Authentication\Exception\NotRelevantForDTO;
use DigitalCraftsman\CQRS\Test\Entity\User;
use DigitalCraftsman\CQRS\Test\ValueObject\UserId;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

final class UserIdValidator implements DTOValidatorInterface
{
    public function __construct(
        private Security $security,
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

        /** @var User $user */
        $user = $this->security->getUser();

        if (!$userId->isEqualTo($user->id)) {
            throw new \DomainException('Invalid user id', Response::HTTP_FORBIDDEN);
        }
    }
}
