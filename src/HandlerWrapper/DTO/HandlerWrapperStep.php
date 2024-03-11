<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\HandlerWrapper\DTO;

use DigitalCraftsman\CQRS\HandlerWrapper\HandlerWrapperInterface;

/**
 * Handler wrappers are used to wrap command handlers and query handlers. With them, it's possible to for example start a doctrine
 * transaction before the handler was executed, commit it when everything worked and roll it back if an exception happens.
 * It's possible to add parameters to them which are used when executing the methods of the steps.
 * Through priorities, it's possible to define in which order they are executed and this order can change depending on the step.
 * To wrap this logic we first combine the wrappers with the parameters and then sort them by the priority of the handlers for the separate
 * steps. After this the controller is able to simply select a step and get all relevant wrappers in the correct order.
 *
 * @codeCoverageIgnore
 *
 * @internal
 */
final readonly class HandlerWrapperStep
{
    public const STEP_PREPARE = 'PREPARE';
    public const STEP_THEN = 'THEN';
    public const STEP_CATCH = 'CATCH';

    /** @var array<class-string<HandlerWrapperInterface>, scalar|array<array-key, scalar|null>|null> */
    public array $orderedHandlerWrapperClasses;

    /**
     * @param array<class-string<HandlerWrapperInterface>, scalar|array<array-key, scalar|null>|null> $handlerWrapperClasses
     * @param self::STEP_*                                                                            $step
     */
    private function __construct(
        array $handlerWrapperClasses,
        string $step,
    ) {
        // Wrappers are sorted descending by priority of the relevant step.
        uksort(
            $handlerWrapperClasses,
            /**
             * @param class-string<HandlerWrapperInterface> $handlerWrapperClassA
             * @param class-string<HandlerWrapperInterface> $handlerWrapperClassB
             */
            static fn (
                string $handlerWrapperClassA,
                string $handlerWrapperClassB,
            ) => self::getPriorityForStep($handlerWrapperClassB, $step) <=> self::getPriorityForStep($handlerWrapperClassA, $step),
        );

        $this->orderedHandlerWrapperClasses = $handlerWrapperClasses;
    }

    /** @param array<class-string<HandlerWrapperInterface>, scalar|array<array-key, scalar|null>|null> $handlerWrapperClasses */
    public static function prepare(array $handlerWrapperClasses): self
    {
        return new self(
            $handlerWrapperClasses,
            self::STEP_PREPARE,
        );
    }

    /** @param array<class-string<HandlerWrapperInterface>, scalar|array<array-key, scalar|null>|null> $handlerWrapperClasses */
    public static function then(array $handlerWrapperClasses): self
    {
        return new self(
            $handlerWrapperClasses,
            self::STEP_THEN,
        );
    }

    /** @param array<class-string<HandlerWrapperInterface>, scalar|array<array-key, scalar|null>|null> $handlerWrapperClasses */
    public static function catch(array $handlerWrapperClasses): self
    {
        return new self(
            $handlerWrapperClasses,
            self::STEP_CATCH,
        );
    }

    /**
     * @param class-string<HandlerWrapperInterface> $handlerWrapperClass
     * @param self::STEP_*                          $step
     */
    private static function getPriorityForStep(string $handlerWrapperClass, string $step): ?int
    {
        return match ($step) {
            self::STEP_PREPARE => $handlerWrapperClass::preparePriority(),
            self::STEP_THEN => $handlerWrapperClass::thenPriority(),
            self::STEP_CATCH => $handlerWrapperClass::catchPriority(),
        };
    }
}
