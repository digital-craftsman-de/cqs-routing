<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Application;

use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\HandlerWrapper\HandlerWrapperInterface;
use DigitalCraftsman\CQRS\Query\Query;
use Symfony\Component\HttpFoundation\Request;

final class SilentExceptionWrapper implements HandlerWrapperInterface
{
    /** @param array<int, string> $parameters */
    public function prepare(
        Command|Query $dto,
        Request $request,
        mixed $parameters,
    ): void {
        // Nothing to do
    }

    /** @param array<int, string> $parameters Exception class strings to be swallowed */
    public function catch(
        Command|Query $dto,
        Request $request,
        mixed $parameters,
        \Exception $exception,
    ): ?\Exception {
        // Catch exception which should be handled silently
        if (in_array(get_class($exception), $parameters, true)) {
            return null;
        }

        return $exception;
    }

    /** @param array<int, string> $parameters */
    public function then(
        Command|Query $dto,
        Request $request,
        mixed $parameters,
    ): void {
        // Nothing to do
    }

    // Priorities

    public static function preparePriority(): int
    {
        return 0;
    }

    public static function catchPriority(): int
    {
        return -100;
    }

    public static function thenPriority(): int
    {
        return 0;
    }

    /** @param array<array-key, class-string<\Throwable>> $parameters */
    public static function areParametersValid(mixed $parameters): bool
    {
        if (!is_array($parameters)) {
            return false;
        }

        foreach ($parameters as $exceptionClass) {
            if (!class_exists($exceptionClass)) {
                return false;
            }

            $reflectionClass = new \ReflectionClass($exceptionClass);
            if (!$reflectionClass->implementsInterface(\Throwable::class)) {
                return false;
            }
        }

        return true;
    }
}
