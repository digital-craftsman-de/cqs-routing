<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Domain\Tasks\ReadSide\GetTasks;

use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\HandlerWrapper\HandlerWrapperInterface;
use DigitalCraftsman\CQRS\Query\Query;
use DigitalCraftsman\CQRS\Test\Utility\LockSimulator;
use Symfony\Component\HttpFoundation\Request;

final class GetTasksHandlerWrapper implements HandlerWrapperInterface
{
    public function __construct(
        private LockSimulator $lockSimulator,
    ) {
    }

    /** @param GetTasksQuery $dto */
    public function prepare(
        Command|Query $dto,
        Request $request,
        mixed $parameters,
    ): void {
        $this->lockSimulator->lockAction((string) $dto->userId);
    }

    /** @param GetTasksQuery $dto */
    public function then(
        Command|Query $dto,
        Request $request,
        mixed $parameters,
    ): void {
        $this->lockSimulator->unlockAction((string) $dto->userId);
    }

    /** @param GetTasksQuery $dto */
    public function catch(
        Command|Query $dto,
        Request $request,
        mixed $parameters,
        \Exception $exception,
    ): ?\Exception {
        $this->lockSimulator->unlockAction((string) $dto->userId);

        return $exception;
    }

    public static function preparePriority(): int
    {
        return 250;
    }

    public static function catchPriority(): int
    {
        return -250;
    }

    public static function thenPriority(): int
    {
        return -250;
    }
}
