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
final class HandlerWrapperStep
{
    public const STEP_PREPARE = 'PREPARE';
    public const STEP_THEN = 'THEN';
    public const STEP_CATCH = 'CATCH';

    /** @var array<int, HandlerWrapperWithParameters> */
    public array $orderedHandlerWrappersWithParameters;

    /**
     * @param array<array-key, HandlerWrapperWithParameters> $handlerWrappersWithParameters
     * @psalm-param self::STEP_* $step
     */
    private function __construct(
        array $handlerWrappersWithParameters,
        string $step,
    ) {
        // Wrappers are sorted descending by priority of the relevant step.
        usort(
            $handlerWrappersWithParameters,
            static fn (HandlerWrapperWithParameters $handlerWrapperWithParametersA, HandlerWrapperWithParameters $handlerWrapperWithParametersB) => self::getPriorityForStep($handlerWrapperWithParametersB->handlerWrapper, $step) <=> self::getPriorityForStep($handlerWrapperWithParametersA->handlerWrapper, $step),
        );

        $this->orderedHandlerWrappersWithParameters = $handlerWrappersWithParameters;
    }

    /** @param array<array-key, HandlerWrapperWithParameters> $handlerWrappersWithParameters */
    public static function prepare(array $handlerWrappersWithParameters): self
    {
        return new self(
            $handlerWrappersWithParameters,
            self::STEP_PREPARE,
        );
    }

    /** @param array<array-key, HandlerWrapperWithParameters> $handlerWrappersWithParameters */
    public static function then(array $handlerWrappersWithParameters): self
    {
        return new self(
            $handlerWrappersWithParameters,
            self::STEP_THEN,
        );
    }

    /** @param array<array-key, HandlerWrapperWithParameters> $handlerWrappersWithParameters */
    public static function catch(array $handlerWrappersWithParameters): self
    {
        return new self(
            $handlerWrappersWithParameters,
            self::STEP_CATCH,
        );
    }

    /** @psalm-param self::STEP_* $step  */
    private static function getPriorityForStep(HandlerWrapperInterface $handlerWrapper, string $step): ?int
    {
        return match ($step) {
            self::STEP_PREPARE => $handlerWrapper::preparePriority(),
            self::STEP_THEN => $handlerWrapper::thenPriority(),
            self::STEP_CATCH => $handlerWrapper::catchPriority(),
            default => throw new \InvalidArgumentException(sprintf('Step %s is not valid', $step)),
        };
    }
}
