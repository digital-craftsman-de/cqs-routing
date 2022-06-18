<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\HandlerWrapper;

use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\Query\Query;
use Symfony\Component\HttpFoundation\Request;

/**
 * When multiple wrappers are defined, the methods are executed in order of the related priorities in descending order. The priorities
 * should be 0 by default and usually range from -256 to 256. Which means that the `prepare` method of a wrapper with priority of 100 for it
 * is executed before one that has a priority of 50 for the same method.
 * This way it's possible to configure a `prepare` method of TransactionWrapper to be executed *before* the method for the LockWrapper,
 * but have the `catch` method of TransactionWrapper be triggered *after* the method of LockWrapper.
 */
interface HandlerWrapperInterface
{
    /**
     * Triggered right before the handler is triggered.
     *
     * @psalm-param array<int, string|int|float|bool>|string|int|float|bool|null $parameters
     */
    public function prepare(
        Command|Query $dto,
        Request $request,
        mixed $parameters,
    ): void;

    /**
     * Triggered only if the handler was run without exception.
     *
     * @psalm-param array<int, string|int|float|bool>|string|int|float|bool|null $parameters
     */
    public function then(
        Command|Query $dto,
        Request $request,
        mixed $parameters,
    ): void;

    /**
     * Triggered only when an exception occurred while executing the handler.
     * The exception must be returned if it's not explicitly the last exception that should be handled.
     *
     * @psalm-param array<int, string|int|float|bool>|string|int|float|bool|null $parameters
     */
    public function catch(
        Command|Query $dto,
        Request $request,
        mixed $parameters,
        \Exception $exception,
    ): ?\Exception;

    public static function preparePriority(): int;

    public static function thenPriority(): int;

    public static function catchPriority(): int;
}
