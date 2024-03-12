<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\HandlerWrapper;

use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\Query\Query;
use Symfony\Component\HttpFoundation\Request;

/**
 * This handler wrapper can be used to swallow exceptions of a certain type as part of the route configuration.
 * As an example imagine a command that marks a task as accepted and has side effects like sending an email notification to the user. When
 * this task is already accepted, the process should be ended (through a thrown exception). But it might not make sense to display an error
 * to the user. With this handler wrapper, you can define the `TaskAlreadyAccepted` exception as one to catch. This would then result in a
 * normal response through the response constructor as if the task was just accepted.
 */
final readonly class SilentExceptionWrapper implements HandlerWrapperInterface
{
    /**
     * @param array<int, string> $parameters
     *
     * @codeCoverageIgnore
     */
    #[\Override]
    public function prepare(
        Command | Query $dto,
        Request $request,
        mixed $parameters,
    ): void {
        // Nothing to do
    }

    /** @param array<int, string> $parameters Exception class strings to be swallowed */
    #[\Override]
    public function catch(
        Command | Query $dto,
        Request $request,
        mixed $parameters,
        \Exception $exception,
    ): ?\Exception {
        // Catch exception which should be handled silently
        if (in_array($exception::class, $parameters, true)) {
            return null;
        }

        return $exception;
    }

    /**
     * @param array<int, string> $parameters
     *
     * @codeCoverageIgnore
     */
    #[\Override]
    public function then(
        Command | Query $dto,
        Request $request,
        mixed $parameters,
    ): void {
        // Nothing to do
    }

    // Priorities

    /**
     * @codeCoverageIgnore
     */
    #[\Override]
    public static function preparePriority(): int
    {
        return 0;
    }

    /**
     * @codeCoverageIgnore
     */
    #[\Override]
    public static function catchPriority(): int
    {
        return -100;
    }

    /**
     * @codeCoverageIgnore
     */
    #[\Override]
    public static function thenPriority(): int
    {
        return 0;
    }

    /** @param array<array-key, class-string<\Throwable>> $parameters Needs to be at least one exception class */
    #[\Override]
    public static function areParametersValid(mixed $parameters): bool
    {
        if (!is_array($parameters)) {
            return false;
        }

        if (count($parameters) === 0) {
            return false;
        }

        foreach ($parameters as $exceptionClass) {
            if (!class_exists($exceptionClass)) {
                return false;
            }

            $reflectionClass = new \ReflectionClass($exceptionClass);
            /** @psalm-suppress TypeDoesNotContainType It's possible that someone puts in something other than an exception. */
            if (!$reflectionClass->implementsInterface(\Throwable::class)) {
                return false;
            }
        }

        return true;
    }
}
