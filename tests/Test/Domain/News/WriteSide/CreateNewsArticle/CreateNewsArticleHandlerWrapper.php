<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle;

use DigitalCraftsman\CQSRouting\Command\Command;
use DigitalCraftsman\CQSRouting\HandlerWrapper\HandlerWrapperInterface;
use DigitalCraftsman\CQSRouting\Query\Query;
use DigitalCraftsman\CQSRouting\Test\Utility\LockSimulator;
use Symfony\Component\HttpFoundation\Request;

final class CreateNewsArticleHandlerWrapper implements HandlerWrapperInterface
{
    public function __construct(
        private readonly LockSimulator $lockSimulator,
    ) {
    }

    /** @param CreateNewsArticleCommand $dto */
    public function prepare(
        Command | Query $dto,
        Request $request,
        mixed $parameters,
    ): void {
        $this->lockSimulator->lockAction((string) $dto->userId);
    }

    /** @param CreateNewsArticleCommand $dto */
    public function then(
        Command | Query $dto,
        Request $request,
        mixed $parameters,
    ): void {
        $this->lockSimulator->unlockAction((string) $dto->userId);
    }

    /** @param CreateNewsArticleCommand $dto */
    public function catch(
        Command | Query $dto,
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

    /** @param null $parameters */
    public static function areParametersValid(mixed $parameters): bool
    {
        return $parameters === null;
    }
}
